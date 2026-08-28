<?php

namespace App\Services\RadioBrowser;

use App\Enums\ArtworkKind;
use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Enums\StreamStatus;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Media;
use App\Models\MediaSource;
use App\Models\SourceProvider;
use App\Models\StreamSource;
use App\Services\Geography\CountryResolver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class RadioBrowserImporter
{
    public function __construct(
        private readonly RadioBrowserClient $client,
        private readonly CountryResolver $countryResolver,
    ) {}

    /** @return array{created: int, updated: int, failed: int} */
    public function import(array $filters = []): array
    {
        $result = ['created' => 0, 'updated' => 0, 'failed' => 0];

        foreach ($this->client->stations($filters) as $station) {
            try {
                $created = $this->importStation($station);
                $result[$created ? 'created' : 'updated']++;
            } catch (Throwable $exception) {
                report($exception);
                $result['failed']++;
            }
        }

        return $result;
    }

    public function importStation(array $station): bool
    {
        $uuid = trim((string) Arr::get($station, 'stationuuid'));
        $name = trim((string) Arr::get($station, 'name'));
        $streamUrl = trim((string) Arr::get($station, 'url'));

        if ($uuid === '' || $name === '' || $streamUrl === '' || ! filter_var($streamUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('A station UUID, name, and valid stream URL are required.');
        }

        return DB::transaction(function () use ($station, $uuid, $name, $streamUrl): bool {
            $provider = SourceProvider::query()->firstOrCreate(
                ['slug' => 'radio-browser'],
                ['name' => 'Radio Browser', 'website_url' => 'https://www.radio-browser.info/', 'is_active' => true],
            );
            $identifierHash = hash('sha256', $uuid);
            $source = MediaSource::query()
                ->where('source_provider_id', $provider->id)
                ->where('external_identifier_hash', $identifierHash)
                ->first();
            $created = $source === null;
            $countryCode = strtoupper(trim((string) Arr::get($station, 'countrycode')));
            $country = $this->countryResolver->resolve($countryCode, (string) Arr::get($station, 'country'));
            $media = $source?->media ?? new Media;
            $displayName = Str::limit($name, 250);
            $media->fill([
                'type' => MediaType::Radio,
                'status' => (int) Arr::get($station, 'lastcheckok') === 1 ? MediaStatus::Published : MediaStatus::Inactive,
                'name' => $displayName,
                'slug' => Str::limit(Str::slug($name), 220, '').'-'.Str::lower(Str::take($uuid, 8)),
                'description' => 'Live radio station listed by Radio Browser.',
                'website_url' => $this->validUrl(Arr::get($station, 'homepage')),
                'country_id' => $country?->id,
            ])->save();

            $media->radioStation()->updateOrCreate(
                ['media_id' => $media->id],
                [
                    'source_state' => trim((string) Arr::get($station, 'state')) ?: null,
                    'latitude' => is_numeric(Arr::get($station, 'geo_lat')) ? Arr::get($station, 'geo_lat') : null,
                    'longitude' => is_numeric(Arr::get($station, 'geo_long')) ? Arr::get($station, 'geo_long') : null,
                    'source_vote_count' => max(0, (int) Arr::get($station, 'votes')),
                    'source_click_count' => max(0, (int) Arr::get($station, 'clickcount')),
                    'source_click_trend' => (int) Arr::get($station, 'clicktrend'),
                    'source_changed_at' => $this->date(Arr::get($station, 'lastchangetime_iso8601')),
                ],
            );

            $metadata = Arr::only($station, [
                'changeuuid', 'country', 'countrycode', 'iso_3166_2', 'state',
                'language', 'languagecodes', 'tags', 'votes', 'clickcount',
                'clicktrend', 'hls', 'ssl_error', 'geo_lat', 'geo_long',
                'has_extended_info', 'lastchangetime_iso8601',
            ]);
            $metadata['rights_verification'] = 'pending';
            $metadata['original_name'] = $name;

            MediaSource::query()->updateOrCreate(
                ['source_provider_id' => $provider->id, 'external_identifier_hash' => $identifierHash],
                [
                    'media_id' => $media->id,
                    'external_identifier' => $uuid,
                    'source_url' => rtrim((string) config('services.radio_browser.base_url'), '/').'/json/stations/byuuid/'.$uuid,
                    'imported_at' => $source?->imported_at ?? now(),
                    'last_synchronized_at' => now(),
                    'metadata' => $metadata,
                ],
            );

            $resolvedUrl = $this->validUrl(Arr::get($station, 'url_resolved'));
            $playbackUrl = $resolvedUrl ?? $streamUrl;
            $isHealthy = (int) Arr::get($station, 'lastcheckok') === 1;
            $codec = trim((string) Arr::get($station, 'codec'));
            $format = (int) Arr::get($station, 'hls') === 1 || str_contains(Str::lower($playbackUrl), '.m3u8')
                ? 'hls'
                : (Str::lower($codec) ?: 'unknown');
            $stream = StreamSource::withTrashed()->firstOrNew([
                'media_id' => $media->id,
                'source_provider_id' => $provider->id,
                'is_primary' => true,
            ]);
            $stream->fill([
                'url' => $streamUrl,
                'resolved_url' => $resolvedUrl,
                'url_hash' => hash('sha256', $streamUrl),
                'protocol' => parse_url($playbackUrl, PHP_URL_SCHEME) ?: 'http',
                'format' => $format,
                'codec' => $codec !== '' ? Str::upper($codec) : null,
                'bitrate_kbps' => max(0, (int) Arr::get($station, 'bitrate')) ?: null,
                'status' => $isHealthy ? StreamStatus::Online : StreamStatus::Offline,
                'verification_status' => VerificationStatus::Pending,
                'last_checked_at' => $this->date(Arr::get($station, 'lastchecktime_iso8601')),
                'last_successful_at' => $this->date(Arr::get($station, 'lastcheckoktime_iso8601')),
                'failure_count' => $isHealthy ? 0 : $stream->failure_count + 1,
            ]);
            $stream->deleted_at = null;
            $stream->save();

            $favicon = $this->validUrl(Arr::get($station, 'favicon'));
            if ($favicon !== null) {
                $media->artworks()->updateOrCreate(
                    ['kind' => ArtworkKind::Logo, 'is_primary' => true],
                    ['url' => $favicon],
                );
            }

            $radioCategory = Category::query()->firstOrCreate(['slug' => 'radio'], ['name' => 'Radio']);
            $media->categories()->syncWithoutDetaching([$radioCategory->id]);
            $this->syncGenres($media, (string) Arr::get($station, 'tags'));
            $this->syncLanguages(
                $media,
                (string) Arr::get($station, 'language'),
                (string) Arr::get($station, 'languagecodes'),
            );

            return $created;
        });
    }

    private function syncGenres(Media $media, string $tags): void
    {
        $genreIds = collect(explode(',', $tags))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique(fn (string $tag): string => Str::lower($tag))
            ->take(12)
            ->map(function (string $tag): int {
                $slug = Str::slug($tag);

                return Genre::query()->firstOrCreate(['slug' => $slug], ['name' => Str::headline($tag)])->id;
            });

        $media->genres()->sync($genreIds);
    }

    private function syncLanguages(Media $media, string $names, string $codes): void
    {
        $languageNames = collect(explode(',', $names))->map(fn (string $name): string => trim($name))->values();
        $languageIds = collect(explode(',', $codes))
            ->map(fn (string $code): string => Str::lower(trim($code)))
            ->map(fn (string $code): ?string => $this->iso6393($code))
            ->filter()
            ->unique()
            ->values()
            ->map(function (string $code, int $index) use ($languageNames): int {
                $name = $languageNames->get($index) ?: \Locale::getDisplayLanguage($code, 'en') ?: Str::upper($code);

                return Language::query()->firstOrCreate(['iso_639_3' => $code], ['name' => Str::headline($name)])->id;
            });

        $media->languages()->sync($languageIds->mapWithKeys(fn (int $id, int $index): array => [
            $id => ['is_primary' => $index === 0],
        ]));
    }

    private function iso6393(string $code): ?string
    {
        if (strlen($code) === 3) {
            return $code;
        }

        return [
            'af' => 'afr', 'ar' => 'ara', 'de' => 'deu', 'en' => 'eng',
            'es' => 'spa', 'fr' => 'fra', 'ha' => 'hau', 'hi' => 'hin',
            'it' => 'ita', 'ja' => 'jpn', 'ko' => 'kor', 'nl' => 'nld',
            'pt' => 'por', 'ru' => 'rus', 'sw' => 'swa', 'tr' => 'tur',
            'yo' => 'yor', 'zh' => 'zho',
        ][$code] ?? null;
    }

    private function validUrl(mixed $value): ?string
    {
        $url = trim((string) $value);

        return $url !== '' && filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    private function date(mixed $value): ?CarbonImmutable
    {
        try {
            return $value ? CarbonImmutable::parse((string) $value) : null;
        } catch (Throwable) {
            return null;
        }
    }
}
