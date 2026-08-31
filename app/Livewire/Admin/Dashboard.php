<?php

namespace App\Livewire\Admin;

use App\Enums\MediaType;
use App\Enums\StreamStatus;
use App\Models\Country;
use App\Models\Media;
use App\Models\StreamReport;
use App\Models\StreamSource;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
    public function render(): View
    {
        $metrics = [
            'Radio stations' => Media::query()->where('type', MediaType::Radio)->count(),
            'TV channels' => Media::query()->where('type', MediaType::Television)->count(),
            'Countries' => Country::query()->whereHas('media')->count(),
            'Healthy streams' => StreamSource::query()->where('status', StreamStatus::Online)->count(),
        ];

        $attention = [
            'Offline streams' => StreamSource::query()->where('status', StreamStatus::Offline)->count(),
            'Unverified streams' => StreamSource::query()->whereNull('last_checked_at')->count(),
            'Open reports' => StreamReport::query()->whereNull('resolved_at')->count(),
        ];

        return view('livewire.admin.dashboard', compact('metrics', 'attention'))
            ->layoutData(['title' => 'Dashboard']);
    }
}
