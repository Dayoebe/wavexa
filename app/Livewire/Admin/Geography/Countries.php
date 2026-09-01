<?php

namespace App\Livewire\Admin\Geography;

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
class Countries extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    public ?int $editingId = null;

    public string $name = '';

    public string $isoAlpha2 = '';

    public string $isoAlpha3 = '';

    public string $isoNumeric = '';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $country = Country::findOrFail($id);
        $this->editingId = $id;
        $this->name = $country->name;
        $this->isoAlpha2 = $country->iso_alpha_2;
        $this->isoAlpha3 = $country->iso_alpha_3 ?? '';
        $this->isoNumeric = $country->iso_numeric ?? '';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'isoAlpha2' => ['required', 'alpha', 'size:2', Rule::unique('countries', 'iso_alpha_2')->ignore($this->editingId)],
            'isoAlpha3' => ['nullable', 'alpha', 'size:3', Rule::unique('countries', 'iso_alpha_3')->ignore($this->editingId)],
            'isoNumeric' => ['nullable', 'digits:3', Rule::unique('countries', 'iso_numeric')->ignore($this->editingId)],
        ]);
        Country::query()->updateOrCreate(['id' => $this->editingId], ['name' => $validated['name'], 'iso_alpha_2' => strtoupper($validated['isoAlpha2']), 'iso_alpha_3' => $validated['isoAlpha3'] ? strtoupper($validated['isoAlpha3']) : null, 'iso_numeric' => $validated['isoNumeric'] ?: null]);
        session()->flash('success', $this->editingId ? 'Country updated.' : 'Country created.');
        $this->cancel();
    }

    public function delete(int $id): void
    {
        $country = Country::query()->withCount(['media', 'administrativeAreas', 'cities'])->findOrFail($id);
        if ($country->media_count + $country->administrative_areas_count + $country->cities_count > 0) {
            $this->addError('delete', 'This country is still used by media, regions, or cities and cannot be deleted.');

            return;
        }
        try {
            $country->delete();
            session()->flash('success', 'Country deleted.');
        } catch (QueryException) {
            $this->addError('delete', 'This country is still used by media, regions, or cities and cannot be deleted.');
        }
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'name', 'isoAlpha2', 'isoAlpha3', 'isoNumeric']);
        $this->resetValidation();
    }

    public function render(): View
    {
        $countries = Country::query()->withCount(['administrativeAreas', 'cities', 'media'])
            ->when($this->q, fn (Builder $query) => $query->where(fn (Builder $query) => $query->where('name', 'like', '%'.$this->q.'%')->orWhere('iso_alpha_2', 'like', '%'.$this->q.'%')->orWhere('iso_alpha_3', 'like', '%'.$this->q.'%')))
            ->orderByRaw("CASE WHEN iso_alpha_2 = 'NG' THEN 0 ELSE 1 END")->orderBy('name')->paginate(25);

        return view('livewire.admin.geography.countries', compact('countries'))->layoutData(['title' => 'Countries']);
    }
}
