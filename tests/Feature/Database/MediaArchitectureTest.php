<?php

namespace Tests\Feature\Database;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Enums\StreamStatus;
use App\Enums\VerificationStatus;
use App\Models\AdministrativeArea;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Language;
use App\Models\Media;
use App\Models\Podcast;
use App\Models\PodcastEpisode;
use App\Models\SourceProvider;
use App\Models\StreamSource;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_reuses_geography_and_taxonomy_relationships(): void
    {
        $country = Country::factory()->create();
        $area = AdministrativeArea::query()->create([
            'country_id' => $country->id,
            'name' => 'Central Region',
            'code' => 'CR',
            'type' => 'region',
        ]);
        $city = City::query()->create([
            'country_id' => $country->id,
            'administrative_area_id' => $area->id,
            'name' => 'Wave City',
        ]);
        $media = Media::factory()->published()->create([
            'country_id' => $country->id,
            'administrative_area_id' => $area->id,
            'city_id' => $city->id,
        ]);
        $category = Category::query()->create(['name' => 'Music', 'slug' => 'music']);
        $language = Language::query()->create([
            'name' => 'English',
            'iso_639_1' => 'en',
            'iso_639_3' => 'eng',
        ]);

        $media->categories()->attach($category);
        $media->languages()->attach($language, ['is_primary' => true]);
        $media->refresh();

        $this->assertTrue($media->country->is($country));
        $this->assertTrue($media->city->is($city));
        $this->assertTrue($media->categories->contains($category));
        $this->assertTrue((bool) $media->languages->first()->pivot->is_primary);
        $this->assertSame(MediaType::Radio, $media->type);
        $this->assertSame(MediaStatus::Published, $media->status);
    }

    public function test_a_podcast_owns_episode_media_records(): void
    {
        $podcastMedia = Media::factory()->podcast()->create();
        $podcast = Podcast::query()->create([
            'media_id' => $podcastMedia->id,
            'feed_url' => 'https://example.test/feed.xml',
            'feed_url_hash' => hash('sha256', 'https://example.test/feed.xml'),
        ]);
        $episodeMedia = Media::factory()->podcastEpisode()->create();
        $episode = PodcastEpisode::query()->create([
            'media_id' => $episodeMedia->id,
            'podcast_id' => $podcast->media_id,
            'guid' => 'episode-1',
            'guid_hash' => hash('sha256', 'episode-1'),
            'duration_seconds' => 1800,
        ]);

        $this->assertTrue($episode->podcast->is($podcast));
        $this->assertTrue($podcast->episodes->contains($episode));
        $this->assertTrue($episode->media->is($episodeMedia));
    }

    public function test_stream_urls_are_unique_per_media_record(): void
    {
        $media = Media::factory()->create();
        $url = 'https://stream.example.test/live.aac';
        $attributes = [
            'media_id' => $media->id,
            'url' => $url,
            'url_hash' => hash('sha256', $url),
            'protocol' => 'https',
            'format' => 'aac',
            'status' => StreamStatus::Unknown,
            'verification_status' => VerificationStatus::Unverified,
        ];

        StreamSource::query()->create($attributes);

        $this->expectException(QueryException::class);
        StreamSource::query()->create($attributes);
    }

    public function test_external_identifiers_are_unique_within_a_provider(): void
    {
        $provider = SourceProvider::query()->create(['name' => 'Directory', 'slug' => 'directory']);
        $identifier = 'station-100';
        $attributes = [
            'source_provider_id' => $provider->id,
            'external_identifier' => $identifier,
            'external_identifier_hash' => hash('sha256', $identifier),
            'imported_at' => now(),
        ];

        Media::factory()->create()->sources()->create($attributes);

        $this->expectException(QueryException::class);
        Media::factory()->create()->sources()->create($attributes);
    }
}
