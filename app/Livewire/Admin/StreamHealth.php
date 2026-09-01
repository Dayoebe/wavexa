<?php

namespace App\Livewire\Admin;

use App\Enums\StreamStatus;
use App\Jobs\CheckStreamHealth;
use App\Models\StreamReport;
use App\Models\StreamSource;
use Illuminate\View\View;
use Illuminate\Support\Facades\Bus;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class StreamHealth extends Component
{
    use WithPagination;

    public function queueStaleChecks(): void
    {
        $ids = StreamSource::query()->orderByRaw('last_checked_at IS NULL DESC')->orderBy('last_checked_at')->limit(100)->pluck('id');
        Bus::batch($ids->map(fn (int $id) => new CheckStreamHealth($id)))->name('Admin stream health review')->dispatch();
        session()->flash('status', $ids->count().' stale stream checks queued.');
    }

    public function render(): View
    {
        $summary = [
            'total' => StreamSource::query()->count(),
            'healthy' => StreamSource::query()->where('status', StreamStatus::Online)->count(),
            'failing' => StreamSource::query()->where('status', StreamStatus::Offline)->count(),
            'unverified' => StreamSource::query()->whereNull('last_checked_at')->count(),
            'reports' => StreamReport::query()->whereNull('resolved_at')->count(),
        ];
        $streams = StreamSource::query()->with(['media.country', 'reports'])
            ->orderByDesc('failure_count')->orderBy('last_checked_at')->paginate(30);

        return view('livewire.admin.stream-health', compact('summary', 'streams'))
            ->layoutData(['title' => 'Stream health']);
    }
}
