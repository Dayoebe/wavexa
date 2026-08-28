<?php

namespace App\Http\Controllers;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Models\Country;
use Illuminate\Contracts\View\View;

class CountryController extends Controller
{
    public function show(string $code): View
    {
        $country = Country::query()->where('iso_alpha_2', strtoupper($code))->firstOrFail();
        $radioStations = $country->media()->where('type', MediaType::Radio)->where('status', MediaStatus::Published)
            ->orderBy('name')->paginate(12, ['*'], 'radio_page')->withQueryString();
        $tvChannels = $country->media()->where('type', MediaType::Television)->where('status', MediaStatus::Published)
            ->orderBy('name')->paginate(12, ['*'], 'tv_page')->withQueryString();
        $radioCount = $country->media()->where('type', MediaType::Radio)->where('status', MediaStatus::Published)->count();
        $tvCount = $country->media()->where('type', MediaType::Television)->where('status', MediaStatus::Published)->count();

        return view('countries.show', compact('country', 'radioStations', 'tvChannels', 'radioCount', 'tvCount'));
    }
}
