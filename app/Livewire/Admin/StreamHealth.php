<?php

namespace App\Livewire\Admin;

use App\Enums\StreamStatus;
use App\Models\StreamReport;
use App\Models\StreamSource;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class StreamHealth extends Component
{
    use WithPagination;

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
