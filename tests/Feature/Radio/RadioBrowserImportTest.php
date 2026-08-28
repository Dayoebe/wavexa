<?php

namespace Tests\Feature\Radio;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Enums\StreamStatus;
use App\Enums\VerificationStatus;
use App\Models\Country;
use App\Models\Language;
use App\Models\Media;
use App\Services\RadioBrowser\RadioBrowserImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RadioBrowserImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_maps_radio_browser_metadata_and_is_idempotent(): void
    {
        Country::query()->create([
            'name' => 'Nigeria',
            'iso_alpha_2' => 'NG',
            'iso_alpha_3' => 'NGA',
            'iso_numeric' => '566',
        ]);
        $importer = app(RadioBrowserImporter::class);

        $this->assertTrue($importer->importStation($this->station()));
        $this->assertFalse($importer->importStation($this->station(['name' => 'Wavexa Test Radio Updated'])));

        $this->assertDatabaseCount('media', 1);
        $this->assertDatabaseCount('radio_stations', 1);
        $this->assertDatabaseCount('media_sources', 1);
        $this->assertDatabaseCount('stream_sources', 1);
        $this->assertDatabaseCount('media_artworks', 1);

        $media = Media::query()->with(['country', 'radioStation', 'genres', 'languages', 'primaryStream'])->firstOrFail();
        $this->assertSame('Wavexa Test Radio Updated', $media->name);
        $this->assertSame(MediaType::Radio, $media->type);
        $this->assertSame(MediaStatus::Published, $media->status);
        $this->assertSame('NG', $media->country->iso_alpha_2);
        $this->assertSame(['Jazz', 'Talk'], $media->genres->pluck('name')->sort()->values()->all());
        $this->assertSame('eng', $media->languages->first()->iso_639_3);
        $this->assertSame('Lagos', $media->radioStation->source_state);
        $this->assertSame(50, $media->radioStation->source_vote_count);
        $this->assertSame(StreamStatus::Online, $media->primaryStream->status);
        $this->assertSame(VerificationStatus::Pending, $media->primaryStream->verification_status);
        $this->assertSame('aac', $media->primaryStream->format);
        $this->assertSame('pending', $media->sources->first()->metadata['rights_verification']);
    }

    public function test_import_command_fetches_only_healthy_filtered_stations(): void
    {
        Http::fake([
            'de1.api.radio-browser.info/json/stations/search*' => Http::response([$this->station()], 200),
        ]);

        $this->artisan('wavexa:import-radio', ['--country' => 'NG', '--limit' => 5])
            ->assertSuccessful();

        Http::assertSent(fn ($request): bool => str_starts_with($request->url(), 'https://de1.api.radio-browser.info/json/stations/search?')
            && $request['countrycode'] === 'NG'
            && $request['hidebroken'] === 'true'
            && $request['limit'] === 5);
        $this->assertDatabaseHas('media_sources', ['external_identifier' => 'station-uuid-100']);
    }

    public function test_import_safely_limits_provider_names_that_exceed_database_columns(): void
    {
        $longName = str_repeat('Very Long Radio Station ', 20);

        app(RadioBrowserImporter::class)->importStation($this->station(['name' => $longName]));

        $media = Media::query()->with('sources')->firstOrFail();
        $this->assertLessThanOrEqual(255, mb_strlen($media->name));
        $this->assertLessThanOrEqual(255, mb_strlen($media->slug));
        $this->assertSame(trim($longName), $media->sources->first()->metadata['original_name']);
    }

    public function test_radio_catalog_and_station_profile_are_publicly_available(): void
    {
        app(RadioBrowserImporter::class)->importStation($this->station());
        $station = Media::query()->firstOrFail();

        $this->get(route('radio.index'))
            ->assertOk()
            ->assertSee('Radio without borders.')
            ->assertSee('Wavexa Test Radio');

        $this->get(route('radio.show', $station->slug))
            ->assertOk()
            ->assertSee('Wavexa Test Radio')
            ->assertSee('Rights review pending')
            ->assertSee('AAC');
    }

    public function test_radio_catalog_exposes_and_applies_discovery_filters(): void
    {
        Country::query()->create([
            'name' => 'Nigeria',
            'iso_alpha_2' => 'NG',
            'iso_alpha_3' => 'NGA',
        ]);
        Country::query()->create([
            'name' => 'South Africa',
            'iso_alpha_2' => 'ZA',
            'iso_alpha_3' => 'ZAF',
        ]);

        $importer = app(RadioBrowserImporter::class);
        $importer->importStation($this->station());
        $importer->importStation($this->station([
            'stationuuid' => 'station-uuid-200',
            'name' => 'Zulu Radio',
            'url' => 'https://streams.example.test/zulu.mp3',
            'country' => 'South Africa',
            'countrycode' => 'ZA',
            'state' => 'Gauteng',
            'language' => 'Afrikaans',
            'languagecodes' => 'af',
            'tags' => 'pop',
            'codec' => 'MP3',
            'votes' => 100,
        ]));

        $this->get(route('radio.index'))
            ->assertOk()
            ->assertSee('Nigeria')
            ->assertSee('South Africa')
            ->assertSee('Lagos')
            ->assertSee('Gauteng')
            ->assertSee('English')
            ->assertSee('Afrikaans')
            ->assertSeeInOrder(['Zulu Radio', 'Wavexa Test Radio']);

        $english = Language::query()->where('iso_639_3', 'eng')->firstOrFail();

        $this->get(route('radio.index', ['country' => 'NG', 'language' => $english->id]))
            ->assertOk()
            ->assertSee('Wavexa Test Radio')
            ->assertDontSee('Zulu Radio');
    }

    public function test_radio_play_event_is_forwarded_to_radio_browser(): void
    {
        Http::fake([
            'de1.api.radio-browser.info/json/url/*' => Http::response(['ok' => true], 200),
        ]);
        app(RadioBrowserImporter::class)->importStation($this->station());
        $station = Media::query()->firstOrFail();

        $this->post(route('radio.play', $station->slug))->assertNoContent();

        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/json/url/station-uuid-100'));
    }

    /** @return array<string, mixed> */
    private function station(array $overrides = []): array
    {
        return array_replace([
            'changeuuid' => 'change-uuid-100',
            'stationuuid' => 'station-uuid-100',
            'name' => 'Wavexa Test Radio',
            'url' => 'https://streams.example.test/wavexa.aac',
            'url_resolved' => 'https://cdn.example.test/wavexa.aac',
            'homepage' => 'https://station.example.test',
            'favicon' => 'https://station.example.test/logo.png',
            'tags' => 'jazz,talk',
            'country' => 'Nigeria',
            'countrycode' => 'NG',
            'iso_3166_2' => 'NG-LA',
            'state' => 'Lagos',
            'language' => 'English',
            'languagecodes' => 'en',
            'votes' => 50,
            'clickcount' => 10,
            'clicktrend' => 2,
            'codec' => 'AAC',
            'bitrate' => 128,
            'hls' => 0,
            'lastcheckok' => 1,
            'lastchecktime_iso8601' => '2026-08-25T12:00:00Z',
            'lastcheckoktime_iso8601' => '2026-08-25T12:00:00Z',
            'lastchangetime_iso8601' => '2026-08-24T12:00:00Z',
            'ssl_error' => 0,
            'geo_lat' => 6.5244,
            'geo_long' => 3.3792,
            'has_extended_info' => false,
        ], $overrides);
    }
}
