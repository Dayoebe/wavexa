<?php

namespace App\Http\Controllers;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Models\Country;
use App\Models\Media;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TvController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'size:2'],
            'sort' => ['nullable', Rule::in(['name_asc', 'name_desc', 'country'])],
        ]);
        $sort = $filters['sort'] ?? 'name_asc';
        $channels = Media::query()
            ->where('type', MediaType::Television)
            ->where('status', MediaStatus::Published)
            ->whereHas('primaryStream')
            ->with(['country', 'tvChannel', 'artworks', 'primaryStream', 'streamSources', 'sources.sourceProvider'])
            ->when($filters['q'] ?? null, fn ($query, string $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($filters['country'] ?? null, fn ($query, string $country) => $query->whereHas(
                'country', fn ($countryQuery) => $countryQuery->where('iso_alpha_2', strtoupper($country)),
            ));

        match ($sort) {
            'name_desc' => $channels->orderByDesc('name'),
            'country' => $channels->orderBy(Country::query()->select('name')->whereColumn('countries.id', 'media.country_id'))->orderBy('name'),
            default => $channels->orderBy('name'),
        };

        $channels = $channels->paginate(18)->withQueryString();
        $countries = Country::query()->select(['id', 'name', 'iso_alpha_2'])->whereHas('media', fn ($query) => $query
            ->where('type', MediaType::Television)->where('status', MediaStatus::Published))
            ->withCount(['media as tv_count' => fn ($query) => $query
                ->where('type', MediaType::Television)->where('status', MediaStatus::Published)])
            ->orderBy('name')->get();
        $recentChannels = Media::query()->where('type', MediaType::Television)->where('status', MediaStatus::Published)
            ->with('country')->latest()->limit(6)->get();

        return view('tv.index', compact('channels', 'countries', 'filters', 'sort', 'recentChannels'));
    }

    public function show(string $slug): View
    {
        $channel = Media::query()->where('type', MediaType::Television)
            ->where('status', MediaStatus::Published)->where('slug', $slug)
            ->with(['country', 'tvChannel', 'artworks', 'primaryStream.sourceProvider', 'streamSources', 'sources.sourceProvider'])
            ->firstOrFail();

        return view('tv.show', compact('channel'));
    }
}
