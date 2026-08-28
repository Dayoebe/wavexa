<?php

namespace App\Jobs;

use App\Enums\StreamStatus;
use App\Models\StreamSource;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Throwable;

class CheckStreamHealth implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 25;

    public function __construct(public readonly int $streamSourceId) {}

    public function handle(): void
    {
        $stream = StreamSource::query()->find($this->streamSourceId);

        if (! $stream) {
            return;
        }

        $startedAt = hrtime(true);

        try {
            $response = Http::withHeaders(['Range' => 'bytes=0-4095'])
                ->withUserAgent('Wavexa-Health-Monitor/1.0')
                ->connectTimeout(5)->timeout(15)->get($stream->resolved_url ?: $stream->url);
            $elapsed = (int) round((hrtime(true) - $startedAt) / 1_000_000);
            $contentType = strtolower(trim(explode(';', $response->header('Content-Type', ''))[0]));
            $isHls = str_contains(strtolower($stream->format), 'hls') || str_contains(strtolower($stream->url), '.m3u8');
            $validBody = ! $isHls || str_starts_with(ltrim($response->body()), '#EXTM3U');
            $healthy = $response->successful() && $validBody;

            $this->record($stream, $healthy, $elapsed, $response->status(), $contentType,
                $healthy ? null : ($validBody ? 'HTTP '.$response->status() : 'Invalid HLS playlist'));
        } catch (Throwable $exception) {
            $this->record($stream, false, (int) round((hrtime(true) - $startedAt) / 1_000_000), null, null, $exception->getMessage());
        }
    }

    private function record(StreamSource $stream, bool $healthy, int $elapsed, ?int $status, ?string $contentType, ?string $reason): void
    {
        $failures = $healthy ? 0 : $stream->failure_count + 1;
        $stream->forceFill([
            'status' => $healthy ? StreamStatus::Online : ($failures >= 3 ? StreamStatus::Offline : StreamStatus::Unknown),
            'failure_count' => $failures,
            'last_checked_at' => now(),
            'last_successful_at' => $healthy ? now() : $stream->last_successful_at,
            'response_time_ms' => $elapsed,
            'http_status' => $status,
            'content_type' => $contentType ?: null,
            'failure_reason' => $healthy ? null : mb_strimwidth((string) $reason, 0, 500),
        ])->save();
    }
}
