<?php

namespace App\Livewire\Admin\Geography;

use App\Models\AdministrativeArea;
use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class Cities extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $countryFilter = '';

    public ?int $editingId = null;

    public string $countryId = '';

    public string $administrativeAreaId = '';

    public string $name = '';

    public string $latitude = '';

    public string $longitude = '';

    public string $timezone = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['q', 'countryFilter'], true)) {
            $this->resetPage();
        } if ($property === 'countryId') {
            $this->administrativeAreaId = '';
        }
    }

    public function edit(int $id): void
    {
        $city = City::findOrFail($id);
        $this->editingId = $id;
        $this->countryId = (string) $city->country_id;
        $this->administrativeAreaId = (string) ($city->administrative_area_id ?? '');
        $this->name = $city->name;
        $this->latitude = (string) ($city->latitude ?? '');
        $this->longitude = (string) ($city->longitude ?? '');
        $this->timezone = $city->timezone ?? '';
    }

    public function save(): void
    {
        $validated = $this->validate(['countryId' => ['required', 'exists:countries,id'], 'administrativeAreaId' => ['nullable', 'exists:administrative_areas,id'], 'name' => ['required', 'string', 'max:255'], 'latitude' => ['nullable', 'numeric', 'between:-90,90'], 'longitude' => ['nullable', 'numeric', 'between:-180,180'], 'timezone' => ['nullable', 'timezone']]);
        if ($validated['administrativeAreaId'] && AdministrativeArea::whereKey($validated['administrativeAreaId'])->where('country_id', '!=', $validated['countryId'])->exists()) {
            $this->addError('administrativeAreaId', 'The region must belong to the selected country.');

            return;
        }
        City::query()->updateOrCreate(['id' => $this->editingId], ['country_id' => $validated['countryId'], 'administrative_area_id' => $validated['administrativeAreaId'] ?: null, 'name' => $validated['name'], 'latitude' => $validated['latitude'] ?: null, 'longitude' => $validated['longitude'] ?: null, 'timezone' => $validated['timezone'] ?: null]);
        session()->flash('success', $this->editingId ? 'City updated.' : 'City created.');
        $this->cancel();
    }

    public function delete(int $id): void
    {
        $city = City::query()->withCount('media')->findOrFail($id);
        if ($city->media_count > 0) {
            $this->addError('delete', 'This city is still assigned to media and cannot be deleted.');

            return;
        }
        try {
            $city->delete();
            session()->flash('success', 'City deleted.');
        } catch (QueryException) {
            $this->addError('delete', 'This city is still assigned to media and cannot be deleted.');
        }
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'countryId', 'administrativeAreaId', 'name', 'latitude', 'longitude', 'timezone']);
        $this->resetValidation();
    }

    public function render(): View
    {
        $cities = City::query()->with(['country', 'administrativeArea'])->withCount('media')->when($this->q, fn (Builder $query) => $query->where('name', 'like', '%'.$this->q.'%'))->when($this->countryFilter, fn (Builder $query) => $query->where('country_id', $this->countryFilter))->orderBy('name')->paginate(25);
        $countries = Country::orderByRaw("CASE WHEN iso_alpha_2 = 'NG' THEN 0 ELSE 1 END")->orderBy('name')->get(['id', 'name']);
        $regions = AdministrativeArea::query()->when($this->countryId, fn ($query) => $query->where('country_id', $this->countryId))->orderBy('name')->get(['id', 'name']);

        return view('livewire.admin.geography.cities', compact('cities', 'countries', 'regions'))->layoutData(['title' => 'Cities']);
    }
}
