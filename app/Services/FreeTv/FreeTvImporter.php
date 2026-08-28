<?php

namespace App\Services\FreeTv;

use App\Enums\ArtworkKind;
use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Enums\StreamStatus;
use App\Enums\VerificationStatus;
use App\Models\Media;
use App\Models\MediaSource;
use App\Models\SourceProvider;
use App\Models\StreamSource;
use App\Services\Geography\CountryResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class FreeTvImporter
{
    public function __construct(
        private readonly FreeTvClient $client,
        private readonly M3uPlaylistParser $parser,
        private readonly CountryResolver $countryResolver,
    ) {}

    /** @return array{created: int, updated: int, skipped: int, failed: int} */
    public function import(?string $countryCode = null, int $limit = 100): array
    {
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];
        $channels = collect($this->parser->parse($this->client->playlist()))
            ->when($countryCode, fn ($items) => $items->where('country_code', strtoupper($countryCode)))
            ->take($limit);

        foreach ($channels as $channel) {
            try {
                if (! $this->isDirectStream($channel['url'])) {
                    $result['skipped']++;

                    continue;
                }

                $result[$this->importChannel($channel) ? 'created' : 'updated']++;
            } catch (Throwable $exception) {
                report($exception);
                $result['failed']++;
            }
        }

        return $result;
    }

    /** @param array<string, string> $channel */
    public function importChannel(array $channel): bool
    {
        return DB::transaction(function () use ($channel): bool {
            $provider = SourceProvider::query()->firstOrCreate(
                ['slug' => 'free-tv'],
                ['name' => 'Free-TV', 'website_url' => 'https://github.com/Free-TV/IPTV', 'is_active' => true],
            );
            $identifierHash = hash('sha256', $channel['id']);
            $source = MediaSource::query()->where('source_provider_id', $provider->id)
                ->where('external_identifier_hash', $identifierHash)->first();
            $country = $this->countryResolver->resolve($channel['country_code'], $channel['group']);
            $media = $source?->media ?? new Media;
            $media->fill([
                'type' => MediaType::Television,
                'status' => MediaStatus::Published,
                'name' => $channel['name'],
                'slug' => Str::slug($channel['name']).'-'.Str::lower(Str::take($identifierHash, 8)),
                'description' => 'Free television channel listed by the Free-TV community directory.',
                'country_id' => $country?->id,
            ])->save();
            $media->tvChannel()->updateOrCreate(['media_id' => $media->id], ['call_sign' => $channel['id']]);

            MediaSource::query()->updateOrCreate(
                ['source_provider_id' => $provider->id, 'external_identifier_hash' => $identifierHash],
                [
                    'media_id' => $media->id,
                    'external_identifier' => $channel['id'],
                    'source_url' => config('services.free_tv.playlist_url'),
                    'imported_at' => $source?->imported_at ?? now(),
                    'last_synchronized_at' => now(),
                    'metadata' => [
                        'group' => $channel['group'], 'channel_number' => $channel['channel_number'],
                        'is_sd' => $channel['is_sd'] === '1', 'is_geoblocked' => $channel['is_geoblocked'] === '1',
                        'rights_verification' => 'pending',
                    ],
                ],
            );

            $stream = StreamSource::withTrashed()->firstOrNew([
                'media_id' => $media->id, 'source_provider_id' => $provider->id, 'is_primary' => true,
            ]);
            $stream->fill([
                'url' => $channel['url'], 'url_hash' => hash('sha256', $channel['url']),
                'protocol' => parse_url($channel['url'], PHP_URL_SCHEME) ?: 'https',
                'format' => str_contains(strtolower(parse_url($channel['url'], PHP_URL_PATH) ?: ''), '.m3u8') ? 'hls' : 'stream',
                'status' => StreamStatus::Unknown, 'verification_status' => VerificationStatus::Pending,
                'is_primary' => true, 'failure_count' => 0,
            ]);
            $stream->deleted_at = null;
            $stream->save();

            if (filter_var($channel['logo'], FILTER_VALIDATE_URL)) {
                $media->artworks()->updateOrCreate(
                    ['kind' => ArtworkKind::Logo, 'is_primary' => true], ['url' => $channel['logo']],
                );
            }

            return $source === null;
        });
    }

    private function isDirectStream(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return ! str_contains($host, 'youtube.com') && ! str_contains($host, 'youtu.be')
            && ! str_contains($host, 'twitch.tv') && ! str_contains($host, 'dailymotion.com');
    }
}
