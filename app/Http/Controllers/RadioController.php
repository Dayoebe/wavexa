<?php

namespace App\Http\Controllers;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Enums\StreamStatus;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Media;
use App\Models\RadioStation;
use App\Models\StreamSource;
use App\Services\RadioBrowser\RadioBrowserClient;
use App\Support\MediaDiscoveryOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Throwable;

class RadioController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'size:2'],
            'state' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'string', Rule::exists('genres', 'slug')],
            'language' => ['nullable', 'integer', Rule::exists('languages', 'id')],
            'codec' => ['nullable', 'string', 'max:32'],
            'sort' => ['nullable', Rule::in(['recommended', 'popular', 'location', 'name_asc', 'name_desc', 'bitrate', 'recent'])],
        ]);

        $sort = $filters['sort'] ?? 'recommended';
        $stations = Media::query()
            ->where('type', MediaType::Radio)
            ->where('status', MediaStatus::Published)
            ->whereHas('primaryStream', fn ($query) => $query->where('status', StreamStatus::Online))
            ->with(['country', 'radioStation', 'genres', 'languages', 'artworks', 'primaryStream', 'streamSources', 'sources.sourceProvider'])
            ->when($filters['q'] ?? null, fn ($query, string $search) => $query->where(function ($searchQuery) use ($search): void {
                $searchQuery->where('name', 'like', '%'.$search.'%')
                    ->orWhereHas('radioStation', fn ($radioQuery) => $radioQuery->where('source_state', 'like', '%'.$search.'%'));
            }))
            ->when($filters['country'] ?? null, fn ($query, string $country) => $query->whereHas(
                'country',
                fn ($countryQuery) => $countryQuery->where('iso_alpha_2', strtoupper($country)),
            ))
            ->when($filters['genre'] ?? null, fn ($query, string $genre) => $query->whereHas(
                'genres',
                fn ($genreQuery) => $genreQuery->where('slug', $genre),
            ))
            ->when($filters['state'] ?? null, fn ($query, string $state) => $query->whereHas(
                'radioStation',
                fn ($radioQuery) => $radioQuery->where('source_state', $state),
            ))
            ->when($filters['language'] ?? null, fn ($query, int $language) => $query->whereHas(
                'languages',
                fn ($languageQuery) => $languageQuery->where('languages.id', $language),
            ))
            ->when($filters['codec'] ?? null, fn ($query, string $codec) => $query->whereHas(
                'primaryStream',
                fn ($streamQuery) => $streamQuery->where('codec', $codec),
            ));

        match ($sort) {
            'recommended' => MediaDiscoveryOrder::countriesFirst($stations)
                ->orderByDesc(RadioStation::query()->select('source_vote_count')->whereColumn('radio_stations.media_id', 'media.id'))
                ->orderBy('name'),
            'location' => $stations
                ->orderBy(Country::query()->select('name')->whereColumn('countries.id', 'media.country_id'))
                ->orderBy(RadioStation::query()->select('source_state')->whereColumn('radio_stations.media_id', 'media.id'))
                ->orderBy('name'),
            'name_asc' => $stations->orderBy('name'),
            'name_desc' => $stations->orderByDesc('name'),
            'bitrate' => $stations
                ->orderByDesc(StreamSource::query()->select('bitrate_kbps')->whereColumn('stream_sources.media_id', 'media.id')->where('is_primary', true)->limit(1))
                ->orderBy('name'),
            'recent' => $stations
                ->orderByDesc(StreamSource::query()->select('last_checked_at')->whereColumn('stream_sources.media_id', 'media.id')->where('is_primary', true)->limit(1))
                ->orderBy('name'),
            default => $stations
                ->orderByDesc(RadioStation::query()->select('source_vote_count')->whereColumn('radio_stations.media_id', 'media.id'))
                ->orderByDesc(RadioStation::query()->select('source_click_trend')->whereColumn('radio_stations.media_id', 'media.id'))
                ->orderBy('name'),
        };

        $stations = $stations
            ->paginate(18)
            ->withQueryString();

        $countries = Country::query()
            ->select(['id', 'name', 'iso_alpha_2'])
            ->whereHas('media', fn ($query) => $query->where('type', MediaType::Radio)->where('status', MediaStatus::Published))
            ->withCount(['media as radio_count' => fn ($query) => $query
                ->where('type', MediaType::Radio)->where('status', MediaStatus::Published)])
            ->orderBy('name')
            ->get();

        $states = RadioStation::query()
            ->whereNotNull('source_state')
            ->where('source_state', '!=', '')
            ->whereHas('media', fn ($query) => $query->where('type', MediaType::Radio)
                ->where('status', MediaStatus::Published)
                ->when($filters['country'] ?? null, fn ($mediaQuery, string $country) => $mediaQuery
                    ->whereHas('country', fn ($countryQuery) => $countryQuery->where('iso_alpha_2', strtoupper($country)))))
            ->distinct()
            ->orderBy('source_state')
            ->pluck('source_state');
        $genres = Genre::query()
            ->select(['id', 'name', 'slug'])
            ->whereHas('media', fn ($query) => $query->where('type', MediaType::Radio)
                ->where('status', MediaStatus::Published)
                ->when($filters['country'] ?? null, fn ($mediaQuery, string $country) => $mediaQuery
                    ->whereHas('country', fn ($countryQuery) => $countryQuery->where('iso_alpha_2', strtoupper($country)))))
            ->withCount(['media as radio_count' => function ($query) use ($filters): void {
                $query->where('type', MediaType::Radio)
                    ->where('status', MediaStatus::Published)
                    ->when($filters['country'] ?? null, fn ($mediaQuery, string $country) => $mediaQuery
                        ->whereHas('country', fn ($countryQuery) => $countryQuery->where('iso_alpha_2', strtoupper($country))));
            }])
            ->orderByDesc('radio_count')->orderBy('name')->limit(80)->get();
        $languages = Language::query()
            ->whereHas('media', fn ($query) => $query->where('type', MediaType::Radio)
                ->where('status', MediaStatus::Published)
                ->when($filters['country'] ?? null, fn ($mediaQuery, string $country) => $mediaQuery
                    ->whereHas('country', fn ($countryQuery) => $countryQuery->where('iso_alpha_2', strtoupper($country)))))
            ->orderBy('name')
            ->get(['id', 'name']);
        $codecs = StreamSource::query()
            ->whereNotNull('codec')
            ->whereHas('media', fn ($query) => $query->where('type', MediaType::Radio)
                ->where('status', MediaStatus::Published)
                ->when($filters['country'] ?? null, fn ($mediaQuery, string $country) => $mediaQuery
                    ->whereHas('country', fn ($countryQuery) => $countryQuery->where('iso_alpha_2', strtoupper($country)))))
            ->distinct()
            ->orderBy('codec')
            ->pluck('codec');
        $trendingStations = Media::query()->where('type', MediaType::Radio)->where('status', MediaStatus::Published)
            ->with(['country', 'radioStation'])->whereHas('primaryStream', fn ($query) => $query->where('status', StreamStatus::Online))
            ->orderByDesc(RadioStation::query()->select('source_click_trend')->whereColumn('radio_stations.media_id', 'media.id'))
            ->limit(6)->get();
        $recentStations = Media::query()->where('type', MediaType::Radio)->where('status', MediaStatus::Published)
            ->with('country')->latest()->limit(6)->get();

        return view('radio.index', compact(
            'stations', 'countries', 'states', 'genres', 'languages', 'codecs', 'filters', 'sort', 'trendingStations', 'recentStations',
        ));
    }

    public function show(string $slug): View
    {
        $station = Media::query()
            ->where('type', MediaType::Radio)
            ->where('status', MediaStatus::Published)
            ->where('slug', $slug)
            ->with([
                'country', 'administrativeArea', 'city', 'genres', 'languages',
                'artworks', 'primaryStream.sourceProvider', 'streamSources', 'sources.sourceProvider',
            ])
            ->firstOrFail();

        return view('radio.show', compact('station'));
    }

    public function play(string $slug, RadioBrowserClient $client): Response
    {
        $source = Media::query()
            ->where('type', MediaType::Radio)
            ->where('status', MediaStatus::Published)
            ->where('slug', $slug)
            ->firstOrFail()
            ->sources()
            ->whereHas('sourceProvider', fn ($query) => $query->where('slug', 'radio-browser'))
            ->firstOrFail();

        try {
            $client->registerClick($source->external_identifier);
        } catch (Throwable $exception) {
            report($exception);
        }

        return response()->noContent();
    }
}
