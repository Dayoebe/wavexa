<?php

namespace App\Jobs;

use App\Models\IngestionRun;
use App\Services\FreeTv\FreeTvImporter;
use App\Services\Podcasts\PodcastImporter;
use App\Services\RadioBrowser\RadioBrowserImporter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class RunIngestion implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public int $tries = 1;

    public function __construct(public int $runId) {}

    public function handle(RadioBrowserImporter $radio, FreeTvImporter $television, PodcastImporter $podcasts): void
    {
        $run = IngestionRun::query()->findOrFail($this->runId);
        $run->update(['status' => 'running', 'started_at' => now(), 'error_message' => null]);

        try {
            $options = $run->options ?? [];
            $result = match ($run->type) {
                'radio' => $radio->import($options),
                'tv' => $television->import($options['country'] ?? null, (int) ($options['limit'] ?? 500)),
                'podcast' => $podcasts->searchAndImport((string) ($options['term'] ?? 'Nigeria'), (string) ($options['country'] ?? 'NG'), (int) ($options['limit'] ?? 25), (int) ($options['episodes'] ?? 25)),
                default => throw new \InvalidArgumentException('Unsupported ingestion type.'),
            };
            $run->update(['status' => 'completed', 'result' => $result, 'finished_at' => now()]);
        } catch (Throwable $exception) {
            $run->update(['status' => 'failed', 'error_message' => mb_substr($exception->getMessage(), 0, 5000), 'finished_at' => now()]);
            report($exception);
        }
    }
}
