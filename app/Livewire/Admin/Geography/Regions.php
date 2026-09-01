<?php

namespace App\Livewire\Admin\Geography;

use App\Models\AdministrativeArea;
use App\Models\Country;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class Regions extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $countryFilter = '';

    public ?int $editingId = null;

    public string $countryId = '';

    public string $parentId = '';

    public string $name = '';

    public string $code = '';

    public string $type = 'region';

    public function updated(string $property): void
    {
        if (in_array($property, ['q', 'countryFilter'], true)) {
            $this->resetPage();
        }
    }

    public function edit(int $id): void
    {
        $area = AdministrativeArea::findOrFail($id);
        $this->editingId = $id;
        $this->countryId = (string) $area->country_id;
        $this->parentId = (string) ($area->parent_id ?? '');
        $this->name = $area->name;
        $this->code = $area->code ?? '';
        $this->type = $area->type ?? 'region';
    }

    public function save(): void
    {
        $validated = $this->validate(['countryId' => ['required', 'exists:countries,id'], 'parentId' => ['nullable', 'exists:administrative_areas,id'], 'name' => ['required', 'string', 'max:255'], 'code' => ['nullable', 'string', 'max:255', Rule::unique('administrative_areas', 'code')->where('country_id', $this->countryId)->ignore($this->editingId)], 'type' => ['nullable', 'string', 'max:100']]);
        if ($validated['parentId'] && AdministrativeArea::whereKey($validated['parentId'])->where('country_id', '!=', $validated['countryId'])->exists()) {
            $this->addError('parentId', 'The parent region must belong to the selected country.');

            return;
        }
        AdministrativeArea::query()->updateOrCreate(['id' => $this->editingId], ['country_id' => $validated['countryId'], 'parent_id' => $validated['parentId'] ?: null, 'name' => $validated['name'], 'code' => $validated['code'] ?: null, 'type' => $validated['type'] ?: null]);
        session()->flash('success', $this->editingId ? 'Region updated.' : 'Region created.');
        $this->cancel();
    }

    public function delete(int $id): void
    {
        $area = AdministrativeArea::query()->withCount(['media', 'cities', 'children'])->findOrFail($id);
        if ($area->media_count + $area->cities_count + $area->children_count > 0) {
            $this->addError('delete', 'This region still has media, cities, or child regions and cannot be deleted.');

            return;
        }
        try {
            $area->delete();
            session()->flash('success', 'Region deleted.');
        } catch (QueryException) {
            $this->addError('delete', 'This region is still referenced and cannot be deleted.');
        }
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'countryId', 'parentId', 'name', 'code']);
        $this->type = 'region';
        $this->resetValidation();
    }

    public function render(): View
    {
        $regions = AdministrativeArea::query()->with(['country', 'parent'])->withCount(['cities', 'children', 'media'])->when($this->q, fn (Builder $query) => $query->where('name', 'like', '%'.$this->q.'%'))->when($this->countryFilter, fn (Builder $query) => $query->where('country_id', $this->countryFilter))->orderBy('name')->paginate(25);
        $countries = Country::orderByRaw("CASE WHEN iso_alpha_2 = 'NG' THEN 0 ELSE 1 END")->orderBy('name')->get(['id', 'name']);
        $parents = AdministrativeArea::query()->when($this->countryId, fn ($query) => $query->where('country_id', $this->countryId))->when($this->editingId, fn ($query) => $query->whereKeyNot($this->editingId))->orderBy('name')->get(['id', 'name']);

        return view('livewire.admin.geography.regions', compact('regions', 'countries', 'parents'))->layoutData(['title' => 'Regions and states']);
    }
}
