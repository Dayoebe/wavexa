<?php

namespace App\Livewire\Pages\Tv;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Models\Country;
use App\Models\Media;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $country = '';

    #[Url(except: 'name_asc')]
    public string $sort = 'name_asc';

    public function applyFilters(): void
    {
        $this->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'size:2'],
            'sort' => ['required', 'in:name_asc,name_desc,country'],
        ]);

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['q', 'country']);
        $this->sort = 'name_asc';
        $this->resetPage();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['q', 'country', 'sort'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $channels = Media::query()
            ->where('type', MediaType::Television)
            ->where('status', MediaStatus::Published)
            ->whereHas('primaryStream')
            ->with(['country', 'tvChannel', 'artworks', 'primaryStream', 'streamSources', 'sources.sourceProvider'])
            ->when($this->q, fn (Builder $query) => $query->where('name', 'like', '%'.$this->q.'%'))
            ->when($this->country, fn (Builder $query) => $query->whereHas(
                'country', fn (Builder $countryQuery) => $countryQuery->where('iso_alpha_2', strtoupper($this->country)),
            ));

        match ($this->sort) {
            'name_desc' => $channels->orderByDesc('name'),
            'country' => $channels->orderBy(Country::query()->select('name')->whereColumn('countries.id', 'media.country_id'))->orderBy('name'),
            default => $channels->orderBy('name'),
        };

        $channels = $channels->paginate(18);
        $countries = Country::query()->select(['id', 'name', 'iso_alpha_2'])
            ->whereHas('media', fn (Builder $query) => $query->where('type', MediaType::Television)->where('status', MediaStatus::Published))
            ->withCount(['media as tv_count' => fn (Builder $query) => $query->where('type', MediaType::Television)->where('status', MediaStatus::Published)])
            ->orderBy('name')->get();
        $recentChannels = Media::query()->where('type', MediaType::Television)->where('status', MediaStatus::Published)
            ->with('country')->latest()->limit(6)->get();
        $title = 'Live TV Around the World — Wavexa';
        $description = 'Discover free television channels by country and watch supported live streams on Wavexa.';
        $canonical = route('tv.index');

        return view('livewire.pages.tv.index', compact('channels', 'countries', 'recentChannels'))
            ->layoutData(compact('title', 'description', 'canonical') + ['structuredData' => Seo::schema($title, $description, $canonical)]);
    }
}
