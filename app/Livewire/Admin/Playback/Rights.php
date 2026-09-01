<?php

namespace App\Livewire\Admin\Playback;

use App\Models\MediaSource;
use App\Models\RightsReview;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class Rights extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: 'all')]
    public string $status = 'all';

    public array $notes = [];

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function review(int $sourceId, string $status): void
    {
        abort_unless(in_array($status, ['approved', 'rejected', 'pending'], true), 422);
        $source = MediaSource::findOrFail($sourceId);
        RightsReview::updateOrCreate(['media_source_id' => $source->id], ['status' => $status, 'note' => $this->notes[$sourceId] ?? null, 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
    }

    public function render(): View
    {
        $sources = MediaSource::query()->when($this->q, fn (Builder $query) => $query->whereHas('media', fn (Builder $media) => $media->where('name', 'like', '%'.$this->q.'%')))->when($this->status === 'pending', fn (Builder $query) => $query->where(fn (Builder $q) => $q->whereDoesntHave('rightsReview')->orWhereHas('rightsReview', fn (Builder $r) => $r->where('status', 'pending'))))->when(in_array($this->status, ['approved', 'rejected'], true), fn (Builder $query) => $query->whereHas('rightsReview', fn (Builder $r) => $r->where('status', $this->status)))->with(['media.country', 'sourceProvider', 'rightsReview.reviewer'])->latest()->paginate(30);

        return view('livewire.admin.playback.rights', compact('sources'))->layoutData(['title' => 'Rights verification']);
    }
}
