<?php

namespace Tests\Feature\Admin;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Enums\StreamStatus;
use App\Enums\VerificationStatus;
use App\Jobs\CheckStreamHealth;
use App\Livewire\Admin\Streams\BrokenReports;
use App\Livewire\Admin\Streams\StreamQueue;
use App\Models\Media;
use App\Models\StreamHealthCheck;
use App\Models\StreamReport;
use App\Models\StreamSource;
use App\Models\User;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class StreamOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_triage_broken_reports_and_queue_a_recheck(): void
    {
        Queue::fake();
        $this->actingAs(User::factory()->admin()->create());
        $stream = $this->stream();
        $report = StreamReport::create(['stream_source_id' => $stream->id, 'reason' => 'buffering', 'details' => 'Stops every minute']);

        Livewire::test(BrokenReports::class)->assertSee('Stops every minute')->call('recheck', $report->id)->call('resolve', $report->id)->assertHasNoErrors();

        Queue::assertPushed(CheckStreamHealth::class, fn (CheckStreamHealth $job) => $job->streamSourceId === $stream->id);
        $this->assertNotNull($report->fresh()->resolved_at);
    }

    public function test_admin_can_verify_sources_and_batch_offline_checks(): void
    {
        Bus::fake();
        $this->actingAs(User::factory()->admin()->create());
        $unverified = $this->stream();
        $offline = $this->stream('Offline TV', StreamStatus::Offline, VerificationStatus::Verified);

        Livewire::test(StreamQueue::class, ['kind' => 'unverified'])->call('verify', $unverified->id)->assertHasNoErrors();
        $this->assertSame(VerificationStatus::Verified, $unverified->fresh()->verification_status);
        Livewire::test(StreamQueue::class, ['kind' => 'offline'])->assertSee('Offline TV')->call('checkBatch')->assertHasNoErrors();
        Bus::assertBatched(fn (PendingBatch $batch) => $batch->jobs->count() === 1 && $batch->jobs->first()->streamSourceId === $offline->id);
    }

    public function test_health_history_is_filterable_and_admin_only(): void
    {
        $stream = $this->stream();
        StreamHealthCheck::create(['stream_source_id' => $stream->id, 'was_healthy' => false, 'http_status' => 503, 'response_time_ms' => 800, 'failure_reason' => 'HTTP 503', 'checked_at' => now()]);
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.streams.history'))->assertOk()->assertSee('Health-check history')->assertSee('Health TV')->assertSee('503');
        auth()->logout();
        $this->actingAs(User::factory()->create())->get(route('admin.streams.reports'))->assertForbidden();
    }

    private function stream(string $name = 'Health TV', StreamStatus $status = StreamStatus::Unknown, VerificationStatus $verification = VerificationStatus::Pending): StreamSource
    {
        $media = Media::create(['type' => MediaType::Television, 'status' => MediaStatus::Published, 'name' => $name, 'slug' => str($name)->slug().'-'.uniqid()]);
        $media->tvChannel()->create();

        return $media->streamSources()->create(['url' => 'https://stream.example.test/'.uniqid().'.m3u8', 'url_hash' => hash('sha256', uniqid()), 'protocol' => 'https', 'format' => 'hls', 'status' => $status, 'verification_status' => $verification, 'is_primary' => true]);
    }
}
