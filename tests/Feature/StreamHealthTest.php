<?php

namespace Tests\Feature;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Enums\StreamStatus;
use App\Enums\VerificationStatus;
use App\Jobs\CheckStreamHealth;
use App\Models\Media;
use App\Models\StreamSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StreamHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_records_success_metrics(): void
    {
        Http::fake(['stream.example.test/*' => Http::response("#EXTM3U\n#EXT-X-VERSION:3", 200, ['Content-Type' => 'application/vnd.apple.mpegurl'])]);
        $stream = $this->stream();
        (new CheckStreamHealth($stream->id))->handle();
        $stream->refresh();
        $this->assertSame(StreamStatus::Online, $stream->status);
        $this->assertSame(200, $stream->http_status);
        $this->assertSame(0, $stream->failure_count);
        $this->assertNotNull($stream->last_successful_at);
    }

    public function test_three_failures_mark_stream_offline_and_public_can_report_it(): void
    {
        Http::fake(['stream.example.test/*' => Http::response('Unavailable', 503)]);
        $stream = $this->stream();
        foreach (range(1, 3) as $_) {
            (new CheckStreamHealth($stream->id))->handle();
        }
        $this->assertSame(StreamStatus::Offline, $stream->refresh()->status);
        $this->postJson(route('streams.report', $stream), ['reason' => 'buffering'])->assertCreated();
        $this->assertDatabaseHas('stream_reports', ['stream_source_id' => $stream->id, 'reason' => 'buffering']);
    }

    public function test_admin_can_view_health_summary(): void
    {
        $this->stream();
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.stream-health'))->assertOk()->assertSee('Stream health');
    }

    private function stream(): StreamSource
    {
        $media = Media::query()->create(['type' => MediaType::Television, 'status' => MediaStatus::Published, 'name' => 'Health TV', 'slug' => 'health-tv']);
        $media->tvChannel()->create();

        return $media->streamSources()->create([
            'url' => 'https://stream.example.test/live.m3u8', 'url_hash' => hash('sha256', 'https://stream.example.test/live.m3u8'),
            'protocol' => 'https', 'format' => 'hls', 'status' => StreamStatus::Unknown,
            'verification_status' => VerificationStatus::Pending, 'is_primary' => true,
        ]);
    }
}
