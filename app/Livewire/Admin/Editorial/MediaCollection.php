<?php

namespace App\Livewire\Admin\Editorial;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Models\EditorialPlacement;
use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class MediaCollection extends Component
{
    use WithPagination;

    public string $collection = 'featured';

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: 'all')]
    public string $type = 'all';

    public string $startsAt = '';

    public string $endsAt = '';

    public string $note = '';

    public function mount(?string $collection = null): void
    {
        abort_unless(in_array($collection, ['featured', 'trending'], true), 404);
        $this->collection = $collection;
    }

    public function updatedQ(): void
    {
        $this->resetPage('candidatesPage');
    }

    public function updatedType(): void
    {
        $this->resetPage('candidatesPage');
    }

    public function add(int $mediaId): void
    {
        $this->validate([
            'startsAt' => ['nullable', 'date'],
            'endsAt' => ['nullable', 'date', 'after:startsAt'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $media = Media::query()->where('status', MediaStatus::Published)->whereIn('type', [MediaType::Radio, MediaType::Television, MediaType::Podcast])->findOrFail($mediaId);
        EditorialPlacement::query()->firstOrCreate(['media_id' => $media->id, 'collection' => $this->collection], [
            'position' => (int) EditorialPlacement::where('collection', $this->collection)->max('position') + 1,
            'starts_at' => $this->startsAt ?: null, 'ends_at' => $this->endsAt ?: null, 'note' => $this->note ?: null,
        ]);
        $this->reset('startsAt', 'endsAt', 'note');
        session()->flash('status', $media->name.' added to '.$this->collection.'.');
    }

    public function remove(int $placementId): void
    {
        EditorialPlacement::query()->where('collection', $this->collection)->findOrFail($placementId)->delete();
        $this->normalizePositions();
    }

    public function move(int $placementId, string $direction): void
    {
        abort_unless(in_array($direction, ['up', 'down'], true), 422);
        $placement = EditorialPlacement::query()->where('collection', $this->collection)->findOrFail($placementId);
        $operator = $direction === 'up' ? '<' : '>';
        $order = $direction === 'up' ? 'desc' : 'asc';
        $neighbor = EditorialPlacement::query()->where('collection', $this->collection)->where('position', $operator, $placement->position)->orderBy('position', $order)->first();
        if ($neighbor) {
            [$placement->position, $neighbor->position] = [$neighbor->position, $placement->position];
            $placement->save();
            $neighbor->save();
        }
    }

    private function normalizePositions(): void
    {
        EditorialPlacement::query()->where('collection', $this->collection)->orderBy('position')->get()->each(fn (EditorialPlacement $item, int $index) => $item->update(['position' => $index + 1]));
    }

    public function render(): View
    {
        $placements = EditorialPlacement::query()->where('collection', $this->collection)->with(['media.country', 'media.artworks'])->orderBy('position')->get();
        $candidates = Media::query()->where('status', MediaStatus::Published)->whereIn('type', [MediaType::Radio, MediaType::Television, MediaType::Podcast])
            ->whereDoesntHave('editorialPlacements', fn (Builder $query) => $query->where('collection', $this->collection))
            ->when($this->type !== 'all', fn (Builder $query) => $query->where('type', $this->type))
            ->when($this->q, fn (Builder $query) => $query->where(fn (Builder $query) => $query->where('name', 'like', '%'.$this->q.'%')->orWhereHas('country', fn (Builder $country) => $country->where('name', 'like', '%'.$this->q.'%'))))
            ->with('country')->latest()->paginate(12, pageName: 'candidatesPage');

        return view('livewire.admin.editorial.media-collection', compact('placements', 'candidates'))->layoutData(['title' => str($this->collection)->headline().' media']);
    }
}
