<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class Assignments extends Component
{
    use WithPagination;

    public string $kind;

    public int $term;

    public Model $taxonomy;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: 'all')]
    public string $type = 'all';

    public function mount(string $kind, int $term): void
    {
        abort_unless(in_array($kind, ['categories', 'genres', 'languages'], true), 404);
        $this->kind = $kind;
        $this->term = $term;
        $model = $this->model();
        $this->taxonomy = $model::query()->findOrFail($term);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['q', 'type'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $base = fn () => Media::query()->whereHas($this->kind, fn (Builder $query) => $query->whereKey($this->term));
        $media = $base()->with(['country', 'radioStation', 'tvChannel', 'podcast', 'primaryStream'])
            ->when($this->q, fn (Builder $query) => $query->where('name', 'like', '%'.$this->q.'%'))
            ->when($this->type !== 'all', fn (Builder $query) => $query->where('type', $this->type))->orderBy('name')->paginate(30);
        $counts = $base()->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type');

        return view('livewire.admin.taxonomy.assignments', compact('media', 'counts'))->layoutData(['title' => $this->taxonomy->name.' assignments']);
    }

    /** @return class-string<Model> */
    private function model(): string
    {
        return match ($this->kind) {
            'categories' => Category::class, 'genres' => Genre::class, 'languages' => Language::class
        };
    }
}
