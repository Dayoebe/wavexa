<?php

namespace App\Services\Podcasts;

use RuntimeException;
use SimpleXMLElement;

class PodcastFeedParser
{
    /** @return array{title: string, description: string, author: string, website: string, artwork: string, language: string, episodes: array<int, array<string, mixed>>} */
    public function parse(string $xml, int $episodeLimit = 25): array
    {
        $previous = libxml_use_internal_errors(true);
        $feed = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $feed?->channel) {
            throw new RuntimeException('The URL did not return a valid podcast RSS feed.');
        }

        $channel = $feed->channel;
        $itunes = $channel->children('http://www.itunes.com/dtds/podcast-1.0.dtd');
        $artwork = (string) ($itunes->image->attributes()->href ?? $channel->image->url ?? '');

        $episodes = [];
        foreach ($channel->item as $item) {
            if (count($episodes) >= $episodeLimit) {
                break;
            }
            $enclosure = $item->enclosure;
            $url = trim((string) ($enclosure['url'] ?? ''));
            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }
            $itemItunes = $item->children('http://www.itunes.com/dtds/podcast-1.0.dtd');
            $guid = trim((string) $item->guid) ?: $url;
            $episodes[] = [
                'title' => trim((string) $item->title) ?: 'Untitled episode',
                'description' => trim(strip_tags((string) ($item->description ?: $itemItunes->summary))),
                'guid' => $guid,
                'published_at' => $this->date((string) $item->pubDate),
                'duration_seconds' => $this->duration((string) $itemItunes->duration),
                'episode_number' => $this->integer((string) $itemItunes->episode),
                'season_number' => $this->integer((string) $itemItunes->season),
                'is_explicit' => in_array(strtolower((string) $itemItunes->explicit), ['yes', 'true', 'explicit'], true),
                'audio_url' => $url,
                'content_type' => trim((string) ($enclosure['type'] ?? 'audio/mpeg')),
                'artwork' => (string) ($itemItunes->image->attributes()->href ?? ''),
            ];
        }

        return [
            'title' => trim((string) $channel->title),
            'description' => trim(strip_tags((string) ($channel->description ?: $itunes->summary))),
            'author' => trim((string) ($itunes->author ?: $channel->managingEditor)),
            'website' => trim((string) $channel->link),
            'artwork' => $artwork,
            'language' => trim((string) $channel->language),
            'episodes' => $episodes,
        ];
    }

    private function duration(string $value): ?int
    {
        if ($value === '') {
            return null;
        }
        if (ctype_digit($value)) {
            return (int) $value;
        }
        $parts = array_map('intval', explode(':', $value));
        if (count($parts) === 3) {
            return $parts[0] * 3600 + $parts[1] * 60 + $parts[2];
        }
        if (count($parts) === 2) {
            return $parts[0] * 60 + $parts[1];
        }

        return null;
    }

    private function integer(string $value): ?int
    {
        return ctype_digit($value) ? (int) $value : null;
    }

    private function date(string $value): ?string
    {
        $timestamp = strtotime($value);

        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }
}
