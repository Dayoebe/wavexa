<?php

namespace App\Services\Podcasts;

use Illuminate\Support\Facades\Http;

class ApplePodcastClient
{
    /** @return array<int, array<string, mixed>> */
    public function search(string $term, string $country = 'NG', int $limit = 25): array
    {
        $response = Http::baseUrl(config('services.apple_podcasts.base_url'))
            ->acceptJson()
            ->withUserAgent(config('services.apple_podcasts.user_agent'))
            ->timeout(config('services.apple_podcasts.timeout'))
            ->retry(2, 500)
            ->get('/search', [
                'term' => $term,
                'country' => strtolower($country),
                'media' => 'podcast',
                'entity' => 'podcast',
                'limit' => min(200, max(1, $limit)),
            ])->throw();

        return collect($response->json('results', []))
            ->filter(fn (array $result): bool => filled($result['feedUrl'] ?? null) && filled($result['collectionId'] ?? null))
            ->values()->all();
    }
}
