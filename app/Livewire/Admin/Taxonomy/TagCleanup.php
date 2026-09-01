<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Models\Genre;
use App\Services\Media\MediaQualityService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class TagCleanup extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    /** @var list<int> */
    public array $selected = [];

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function cleanSelected(): void
    {
        $ids = collect($this->selected)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            $this->addError('selected', 'Select at least one suspicious tag.');

            return;
        }
        DB::transaction(function () use ($ids): void {
            Genre::query()->whereIn('id', $ids)->each(function (Genre $genre): void {
                $genre->media()->detach();
                $genre->delete();
            });
        });
        session()->flash('success', $ids->count().' noisy tags removed from media and deleted.');
        $this->selected = [];
    }

    public function cleanAll(MediaQualityService $quality): void
    {
        $ids = Genre::query()->get(['id', 'name'])->filter(fn (Genre $genre) => $quality->isNoisyGenre($genre->name))->modelKeys();
        $this->selected = $ids;
        $this->cleanSelected();
    }

    public function render(MediaQualityService $quality): View
    {
        $all = Genre::query()->withCount('media')->when($this->q, fn ($query) => $query->where('name', 'like', '%'.$this->q.'%'))->orderByDesc('media_count')->get()->filter(fn (Genre $genre) => $quality->isNoisyGenre($genre->name))->values();
        $page = $this->getPage();
        $perPage = 30;
        $genres = new LengthAwarePaginator($all->forPage($page, $perPage), $all->count(), $perPage, $page, ['path' => request()->url(), 'pageName' => 'page']);

        return view('livewire.admin.taxonomy.tag-cleanup', compact('genres'))->layoutData(['title' => 'Tag cleanup']);
    }
}
