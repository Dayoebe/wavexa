<?php

namespace Tests\Feature\Admin;

use App\Jobs\RunIngestion;
use App\Livewire\Admin\Ingestion\History;
use App\Livewire\Admin\Ingestion\ImportWorkspace;
use App\Livewire\Admin\Ingestion\Sources;
use App\Models\IngestionRun;
use App\Models\SourceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class IngestionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_queue_bounded_imports_for_each_supported_source(): void
    {
        Queue::fake();
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(ImportWorkspace::class, ['type' => 'radio'])->set('country', 'NG')->set('limit', 50)->call('queueImport')->assertHasNoErrors();
        Livewire::test(ImportWorkspace::class, ['type' => 'tv'])->set('country', 'US')->set('limit', 250)->call('queueImport')->assertHasNoErrors();
        Livewire::test(ImportWorkspace::class, ['type' => 'podcast'])->set('term', 'Technology')->set('country', 'GB')->set('limit', 10)->set('episodes', 15)->call('queueImport')->assertHasNoErrors();

        $this->assertDatabaseCount('ingestion_runs', 3);
        $this->assertDatabaseHas('ingestion_runs', ['type' => 'radio', 'status' => 'queued']);
        Queue::assertPushed(RunIngestion::class, 3);
    }

    public function test_disabled_provider_blocks_new_imports(): void
    {
        Queue::fake();
        $this->actingAs(User::factory()->admin()->create());
        SourceProvider::create(['name' => 'Radio Browser', 'slug' => 'radio-browser', 'website_url' => 'https://www.radio-browser.info/', 'is_active' => false]);

        Livewire::test(ImportWorkspace::class, ['type' => 'radio'])->call('queueImport')->assertHasErrors('source');
        $this->assertDatabaseCount('ingestion_runs', 0);
        Queue::assertNothingPushed();
    }

    public function test_admin_can_toggle_sources_and_retry_a_historical_run(): void
    {
        Queue::fake();
        $this->actingAs(User::factory()->admin()->create());
        $provider = SourceProvider::create(['name' => 'Free-TV', 'slug' => 'free-tv', 'website_url' => 'https://github.com/Free-TV/IPTV', 'is_active' => true]);
        $run = IngestionRun::create(['source_provider_id' => $provider->id, 'type' => 'tv', 'status' => 'failed', 'options' => ['country' => 'NG', 'limit' => 25]]);

        Livewire::test(Sources::class)->call('toggle', $provider->id);
        $this->assertFalse($provider->fresh()->is_active);
        Livewire::test(History::class)->call('retry', $run->id)->assertHasErrors('source');
        Queue::assertNothingPushed();
        Livewire::test(Sources::class)->call('toggle', $provider->id);
        Livewire::test(History::class)->call('retry', $run->id)->assertHasNoErrors();
        $this->assertDatabaseHas('ingestion_runs', ['type' => 'tv', 'status' => 'queued']);
        Queue::assertPushed(RunIngestion::class);
    }

    public function test_ingestion_routes_require_an_administrator(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.ingestion.history'))->assertOk()->assertSee('Import history');
        auth()->logout();
        $this->actingAs(User::factory()->create())->get(route('admin.ingestion.sources'))->assertForbidden();
    }
}
