<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Language;
use App\Services\Taxonomy\TaxonomyService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class Terms extends Component
{
    use WithPagination;

    public string $kind = 'categories';

    #[Url(except: '')]
    public string $q = '';

    public ?int $editingId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public string $iso6391 = '';

    public string $iso6393 = '';

    public string $mergeTargetId = '';

    public function mount(?string $kind = null): void
    {
        abort_unless(in_array($kind, ['categories', 'genres', 'languages'], true), 404);
        $this->kind = $kind;
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedName(): void
    {
        if (! $this->editingId && $this->kind !== 'languages') {
            $this->slug = str($this->name)->slug()->toString();
        }
    }

    public function edit(int $id): void
    {
        $term = $this->query()->findOrFail($id);
        $this->editingId = $id;
        $this->name = $term->name;
        $this->slug = $term->slug ?? '';
        $this->description = $term->description ?? '';
        $this->iso6391 = $term->iso_639_1 ?? '';
        $this->iso6393 = $term->iso_639_3 ?? '';
        $this->mergeTargetId = '';
    }

    public function save(): void
    {
        $rules = ['name' => ['required', 'string', 'max:255']];
        if ($this->kind !== 'languages') {
            $rules += ['slug' => ['required', 'alpha_dash', 'max:255', Rule::unique($this->kind, 'slug')->ignore($this->editingId)]];
        }
        if ($this->kind === 'categories') {
            $rules += ['description' => ['nullable', 'string', 'max:5000']];
        }
        if ($this->kind === 'languages') {
            $rules += ['iso6391' => ['nullable', 'alpha', 'size:2', Rule::unique('languages', 'iso_639_1')->ignore($this->editingId)], 'iso6393' => ['required', 'alpha', 'size:3', Rule::unique('languages', 'iso_639_3')->ignore($this->editingId)]];
        }
        $this->validate($rules);
        $attributes = ['name' => $this->name];
        if ($this->kind !== 'languages') {
            $attributes['slug'] = str($this->slug)->slug()->toString();
        }
        if ($this->kind === 'categories') {
            $attributes['description'] = $this->description ?: null;
        }
        if ($this->kind === 'languages') {
            $attributes['iso_639_1'] = $this->iso6391 ? strtolower($this->iso6391) : null;
            $attributes['iso_639_3'] = strtolower($this->iso6393);
        }
        $model = $this->model();
        $model::query()->updateOrCreate(['id' => $this->editingId], $attributes);
        session()->flash('success', str($this->kind)->singular()->headline().' saved.');
        $this->cancel();
    }

    public function merge(TaxonomyService $service): void
    {
        $this->validate(['mergeTargetId' => ['required', 'integer', Rule::exists($this->kind, 'id')->where(fn ($query) => $query->where('id', '!=', $this->editingId))]]);
        $service->merge($this->kind, $this->editingId, (int) $this->mergeTargetId);
        session()->flash('success', 'Terms merged and media assignments preserved.');
        $this->cancel();
    }

    public function delete(int $id, TaxonomyService $service): void
    {
        if (! $service->deleteUnused($this->kind, $id)) {
            $this->addError('delete', 'This term is assigned to media. Merge it into another term instead of deleting it.');

            return;
        }
        session()->flash('success', 'Unused term deleted.');
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'name', 'slug', 'description', 'iso6391', 'iso6393', 'mergeTargetId']);
        $this->resetValidation();
    }

    public function render(): View
    {
        $items = $this->query()->withCount('media')->when($this->q, fn (Builder $query) => $query->where('name', 'like', '%'.$this->q.'%'))->orderByDesc('media_count')->orderBy('name')->paginate(30);
        $targets = $this->query()->when($this->editingId, fn (Builder $query) => $query->whereKeyNot($this->editingId))->orderBy('name')->get(['id', 'name']);

        return view('livewire.admin.taxonomy.terms', compact('items', 'targets'))->layoutData(['title' => str($this->kind)->headline()->toString()]);
    }

    private function query(): Builder
    {
        $model = $this->model();

        return $model::query();
    }

    private function model(): string
    {
        return match ($this->kind) {
            'categories' => Category::class,'genres' => Genre::class,'languages' => Language::class
        };
    }
}
