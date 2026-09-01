<?php

namespace Tests\Feature;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Models\Genre;
use App\Models\Media;
use App\Models\SearchQuery;
use App\Models\User;
use App\Services\Search\SearchAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_search_finds_media_by_name_and_taxonomy_and_records_analytics(): void
    {
        $station = Media::factory()->create(['name' => 'Lagos Jazz Radio', 'type' => MediaType::Radio, 'status' => MediaStatus::Published]);
        $genre = Genre::query()->create(['name' => 'Jazz', 'slug' => 'jazz']);
        $station->genres()->attach($genre);

        $this->get(route('search', ['q' => 'Jazz']))->assertOk()->assertSee('Lagos Jazz Radio')->assertSee('Open radio');
        $this->assertDatabaseHas('search_queries', ['normalized_query' => 'jazz', 'results_count' => 1, 'context' => 'global']);
        $record = SearchQuery::firstOrFail();
        $this->assertSame(64, strlen($record->ip_hash));
        $this->assertNotSame('127.0.0.1', $record->ip_hash);
    }

    public function test_admin_can_review_popular_and_no_result_searches(): void
    {
        app(SearchAnalytics::class)->record('Lagos', 5);
        app(SearchAnalytics::class)->record('Lagos', 3);
        app(SearchAnalytics::class)->record('Missing station', 0);
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.search.index'))->assertOk()->assertSee('Searchable catalogue');
        $this->get(route('admin.search.popular'))->assertOk()->assertSee('Lagos')->assertSee('2');
        $this->get(route('admin.search.no-results'))->assertOk()->assertSee('Missing station');
    }

    public function test_one_character_queries_are_not_recorded(): void
    {
        $this->get(route('search', ['q' => 'a']))->assertOk()->assertSee('Type at least two characters');
        $this->assertDatabaseCount('search_queries', 0);
    }
}
