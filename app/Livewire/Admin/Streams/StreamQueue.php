<?php

namespace App\Livewire\Admin\Streams;

use App\Enums\StreamStatus;
use App\Enums\VerificationStatus;
use App\Jobs\CheckStreamHealth;
use App\Models\StreamSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Bus;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class StreamQueue extends Component
{
    use WithPagination;

    public string $kind = 'unverified';

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: 'all')]
    public string $type = 'all';

    public function mount(?string $kind = null): void
    {
        abort_unless(in_array($kind, ['unverified', 'offline'], true), 404);
        $this->kind = $kind;
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function recheck(int $streamId): void
    {
        $this->baseQuery()->findOrFail($streamId);
        CheckStreamHealth::dispatch($streamId);
        session()->flash('status', 'Stream check queued.');
    }

    public function checkBatch(): void
    {
        $ids = $this->baseQuery()->orderBy('last_checked_at')->limit(100)->pluck('id');
        Bus::batch($ids->map(fn (int $id) => new CheckStreamHealth($id)))->name(str($this->kind)->headline().' stream review')->dispatch();
        session()->flash('status', $ids->count().' stream checks queued.');
    }

    public function verify(int $streamId): void
    {
        abort_unless($this->kind === 'unverified', 422);
        $this->baseQuery()->findOrFail($streamId)->update(['verification_status' => VerificationStatus::Verified]);
    }

    public function reject(int $streamId): void
    {
        abort_unless($this->kind === 'unverified', 422);
        $this->baseQuery()->findOrFail($streamId)->update(['verification_status' => VerificationStatus::Rejected]);
    }

    private function baseQuery(): Builder
    {
        return StreamSource::query()->when($this->kind === 'offline', fn (Builder $query) => $query->where('status', StreamStatus::Offline), fn (Builder $query) => $query->whereIn('verification_status', [VerificationStatus::Unverified, VerificationStatus::Pending]))
            ->when($this->type !== 'all', fn (Builder $query) => $query->whereHas('media', fn (Builder $media) => $media->where('type', $this->type)))
            ->when($this->q, fn (Builder $query) => $query->whereHas('media', fn (Builder $media) => $media->where('name', 'like', '%'.$this->q.'%')));
    }

    public function render(): View
    {
        $streams = $this->baseQuery()->with(['media.country', 'sourceProvider'])->orderByDesc('failure_count')->orderBy('last_checked_at')->paginate(30);

        return view('livewire.admin.streams.stream-queue', compact('streams'))->layoutData(['title' => str($this->kind)->headline().' streams']);
    }
}
