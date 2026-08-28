<?php

namespace App\Services\RadioBrowser;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Arr;
use RuntimeException;

class RadioBrowserClient
{
    public function __construct(private readonly HttpFactory $http) {}

    /** @return list<array<string, mixed>> */
    public function stations(array $filters = []): array
    {
        $response = $this->http
            ->baseUrl((string) config('services.radio_browser.base_url'))
            ->acceptJson()
            ->withUserAgent((string) config('services.radio_browser.user_agent'))
            ->connectTimeout(5)
            ->timeout((int) config('services.radio_browser.timeout'))
            ->retry(2, 250)
            ->get('/json/stations/search', array_filter([
                'countrycode' => Arr::get($filters, 'country'),
                'name' => Arr::get($filters, 'name'),
                'tag' => Arr::get($filters, 'tag'),
                'language' => Arr::get($filters, 'language'),
                'hidebroken' => 'true',
                'order' => Arr::get($filters, 'order', 'votes'),
                'reverse' => 'true',
                'offset' => Arr::get($filters, 'offset', 0),
                'limit' => Arr::get($filters, 'limit', 100),
            ], fn (mixed $value): bool => $value !== null && $value !== ''));

        $stations = $response->throw()->json();

        if (! is_array($stations)) {
            throw new RuntimeException('Radio Browser returned an invalid station response.');
        }

        return array_values(array_filter($stations, 'is_array'));
    }

    public function registerClick(string $stationUuid): void
    {
        $this->http
            ->baseUrl((string) config('services.radio_browser.base_url'))
            ->acceptJson()
            ->withUserAgent((string) config('services.radio_browser.user_agent'))
            ->connectTimeout(3)
            ->timeout(5)
            ->get('/json/url/'.rawurlencode($stationUuid))
            ->throw();
    }
}
