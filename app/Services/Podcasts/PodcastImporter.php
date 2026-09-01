<?php

namespace App\Services\Podcasts;

use App\Enums\ArtworkKind;
use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Enums\StreamStatus;
use App\Enums\VerificationStatus;
use App\Models\Media;
use App\Models\MediaSource;
use App\Models\Podcast;
use App\Models\SourceProvider;
use App\Services\Geography\CountryResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class PodcastImporter
{
    public function __construct(
        private readonly ApplePodcastClient $client,
        private readonly PodcastFeedParser $parser,
        private readonly CountryResolver $countryResolver,
    ) {}

    /** @return array{created: int, updated: int, episodes: int, failed: int} */
    public function searchAndImport(string $term, string $country, int $limit, int $episodeLimit): array
    {
        $result = ['created' => 0, 'updated' => 0, 'episodes' => 0, 'failed' => 0];
        foreach ($this->client->search($term, $country, $limit) as $directoryItem) {
            try {
                $imported = $this->importFeed((string) $directoryItem['feedUrl'], $country, $directoryItem, $episodeLimit);
                $result[$imported['podcast'] ? 'created' : 'updated']++;
                $result['episodes'] += $imported['episodes'];
            } catch (Throwable $exception) {
                report($exception);
                $result['failed']++;
            }
        }

        return $result;
    }

    /** @param array<string, mixed> $directoryItem
     * @return array{podcast: bool, episodes: int}
     */
    public function importFeed(string $feedUrl, ?string $countryCode = null, array $directoryItem = [], int $episodeLimit = 25): array
    {
        $response = Http::withUserAgent(config('services.apple_podcasts.user_agent'))
            ->timeout(config('services.apple_podcasts.timeout'))->retry(2, 500)->get($feedUrl)->throw();
        $feed = $this->parser->parse($response->body(), $episodeLimit);
        $feedHash = hash('sha256', $feedUrl);
        $existing = Podcast::query()->where('feed_url_hash', $feedHash)->first();
        $country = $countryCode ? $this->countryResolver->resolve($countryCode, null) : null;

        return DB::transaction(function () use ($feed, $feedUrl, $feedHash, $existing, $country, $directoryItem): array {
            $provider = SourceProvider::query()->firstOrCreate(
                ['slug' => 'apple-podcasts'],
                ['name' => 'Apple Podcasts Directory', 'website_url' => 'https://podcasts.apple.com', 'is_active' => true],
            );
            $media = $existing?->media ?? new Media;
            $title = $feed['title'] ?: ($directoryItem['collectionName'] ?? 'Untitled podcast');
            $media->fill([
                'type' => MediaType::Podcast,
                'status' => MediaStatus::Published,
                'name' => $title,
                'slug' => $media->exists ? $media->slug : $this->uniqueSlug($title, MediaType::Podcast),
                'description' => $feed['description'] ?: null,
                'website_url' => filter_var($feed['website'], FILTER_VALIDATE_URL) ? $feed['website'] : null,
                'country_id' => $country?->id,
            ])->save();
            $podcast = $media->podcast()->updateOrCreate(['media_id' => $media->id], [
                'feed_url' => $feedUrl, 'feed_url_hash' => $feedHash,
                'author' => $feed['author'] ?: ($directoryItem['artistName'] ?? null), 'last_fetched_at' => now(),
            ]);

            $externalId = (string) ($directoryItem['collectionId'] ?? $feedHash);
            MediaSource::query()->updateOrCreate(
                ['source_provider_id' => $provider->id, 'external_identifier_hash' => hash('sha256', $externalId)],
                ['media_id' => $media->id, 'external_identifier' => $externalId, 'source_url' => $feedUrl,
                    'imported_at' => now(), 'last_synchronized_at' => now(), 'metadata' => ['directory_url' => $directoryItem['collectionViewUrl'] ?? null]],
            );
            $this->artwork($media, $feed['artwork'] ?: ($directoryItem['artworkUrl600'] ?? ''));

            $episodeCount = 0;
            foreach ($feed['episodes'] as $episode) {
                $this->episode($podcast, $episode, $provider->id);
                $episodeCount++;
            }

            return ['podcast' => $existing === null, 'episodes' => $episodeCount];
        });
    }

    /** @param array<string, mixed> $episode */
    private function episode(Podcast $podcast, array $episode, int $providerId): void
    {
        $guidHash = hash('sha256', $episode['guid']);
        $existing = $podcast->episodes()->where('guid_hash', $guidHash)->first();
        $media = $existing?->media ?? new Media;
        $media->fill([
            'type' => MediaType::PodcastEpisode, 'status' => MediaStatus::Published,
            'name' => $episode['title'],
            'slug' => $media->exists ? $media->slug : $this->uniqueSlug($episode['title'].'-'.$podcast->media_id, MediaType::PodcastEpisode),
            'description' => $episode['description'] ?: null,
            'country_id' => $podcast->media->country_id,
        ])->save();
        $media->podcastEpisode()->updateOrCreate(['media_id' => $media->id], [
            'podcast_id' => $podcast->media_id, 'guid' => $episode['guid'], 'guid_hash' => $guidHash,
            'season_number' => $episode['season_number'], 'episode_number' => $episode['episode_number'],
            'duration_seconds' => $episode['duration_seconds'], 'published_at' => $episode['published_at'],
            'is_explicit' => $episode['is_explicit'],
        ]);
        $media->streamSources()->updateOrCreate(['is_primary' => true], [
            'source_provider_id' => $providerId, 'url' => $episode['audio_url'],
            'url_hash' => hash('sha256', $episode['audio_url']), 'protocol' => parse_url($episode['audio_url'], PHP_URL_SCHEME) ?: 'https',
            'format' => $this->format($episode['content_type'], $episode['audio_url']), 'status' => StreamStatus::Unknown,
            'verification_status' => VerificationStatus::Pending, 'failure_count' => 0,
        ]);
        $this->artwork($media, $episode['artwork']);
    }

    private function artwork(Media $media, string $url): void
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $media->artworks()->updateOrCreate(['kind' => ArtworkKind::Cover, 'is_primary' => true], ['url' => $url]);
        }
    }

    private function uniqueSlug(string $name, MediaType $type): string
    {
        $base = Str::slug($name) ?: 'podcast';
        $slug = $base;
        $suffix = 2;
        while (Media::withTrashed()->where('type', $type)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function format(string $contentType, string $url): string
    {
        $value = strtolower($contentType.' '.parse_url($url, PHP_URL_PATH));

        return match (true) {
            str_contains($value, 'mp4'), str_contains($value, 'm4a') => 'm4a', str_contains($value, 'ogg') => 'ogg', default => 'mp3'
        };
    }
}
