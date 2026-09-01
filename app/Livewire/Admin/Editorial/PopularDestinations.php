<?php

namespace App\Livewire\Admin\Editorial;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Models\Country;
use App\Models\CountryPromotion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class PopularDestinations extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function promote(int $countryId): void
    {
        Country::query()->findOrFail($countryId);
        CountryPromotion::query()->firstOrCreate(['country_id' => $countryId], ['position' => (int) CountryPromotion::max('position') + 1]);
    }

    public function remove(int $countryId): void
    {
        CountryPromotion::query()->where('country_id', $countryId)->delete();
        $this->normalizePositions();
    }

    public function move(int $countryId, string $direction): void
    {
        abort_unless(in_array($direction, ['up', 'down'], true), 422);
        $item = CountryPromotion::query()->findOrFail($countryId);
        $neighbor = CountryPromotion::query()->where('position', $direction === 'up' ? '<' : '>', $item->position)->orderBy('position', $direction === 'up' ? 'desc' : 'asc')->first();
        if ($neighbor) {
            [$item->position, $neighbor->position] = [$neighbor->position, $item->position];
            $item->save();
            $neighbor->save();
        }
    }

    private function normalizePositions(): void
    {
        CountryPromotion::query()->orderBy('position')->get()->each(fn (CountryPromotion $item, int $index) => $item->update(['position' => $index + 1]));
    }

    public function render(): View
    {
        $count = fn (Builder $query, MediaType $type) => $query->where('status', MediaStatus::Published)->where('type', $type);
        $promotions = CountryPromotion::query()->with('country')->orderBy('position')->get();
        $countries = Country::query()->when($this->q, fn (Builder $query) => $query->where('name', 'like', '%'.$this->q.'%'))
            ->withCount(['media as radio_count' => fn (Builder $query) => $count($query, MediaType::Radio), 'media as tv_count' => fn (Builder $query) => $count($query, MediaType::Television), 'media as podcast_count' => fn (Builder $query) => $count($query, MediaType::Podcast)])
            ->orderByRaw('radio_count + tv_count + podcast_count DESC')->paginate(30);

        return view('livewire.admin.editorial.popular-destinations', compact('promotions', 'countries'))->layoutData(['title' => 'Popular destinations']);
    }
}
