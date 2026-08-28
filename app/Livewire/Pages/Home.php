<?php

namespace App\Livewire\Pages;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Enums\StreamStatus;
use App\Models\Country;
use App\Models\Media;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Home extends Component
{
    public function render(): View
    {
        $eligible = fn (Builder $query) => $query
            ->where('status', MediaStatus::Published)
            ->whereHas('primaryStream', fn (Builder $stream) => $stream->where('status', '!=', StreamStatus::Offline));

        $radioCount = Media::query()->where('type', MediaType::Radio)->where($eligible)->count();
        $tvCount = Media::query()->where('type', MediaType::Television)->where($eligible)->count();
        $countryCount = Country::query()->whereHas('media', $eligible)->count();
        $countries = Country::query()->whereHas('media', $eligible)->withCount([
            'media as radio_count' => fn (Builder $query) => $query->where('type', MediaType::Radio)->where($eligible),
            'media as tv_count' => fn (Builder $query) => $query->where('type', MediaType::Television)->where($eligible),
        ])->orderByRaw('radio_count + tv_count DESC')->limit(8)->get();
        $radioStations = Media::query()->where('type', MediaType::Radio)->where($eligible)
            ->with(['country', 'artworks', 'primaryStream', 'streamSources'])->latest()->limit(4)->get();
        $tvChannels = Media::query()->where('type', MediaType::Television)->where($eligible)
            ->with(['country', 'artworks'])->latest()->limit(4)->get();
        $title = 'Wavexa — Live radio and television from around the world';
        $description = 'Move through the world by sound and screen. Discover live radio stations and supported television channels by country on Wavexa.';
        $canonical = route('home');

        return view('livewire.pages.home', compact('radioCount', 'tvCount', 'countryCount', 'countries', 'radioStations', 'tvChannels'))
            ->layoutData(compact('title', 'description', 'canonical') + [
                'structuredData' => Seo::schema($title, $description, $canonical),
            ]);
    }
}
