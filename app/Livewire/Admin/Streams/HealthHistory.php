<?php

namespace App\Livewire\Admin\Streams;

use App\Models\StreamHealthCheck;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class HealthHistory extends Component
{
    use WithPagination;

    #[Url(except: 'all')] public string $result = 'all';
    #[Url(except: '')] public string $q = '';

    public function updatedResult(): void { $this->resetPage(); }
    public function updatedQ(): void { $this->resetPage(); }

    public function render(): View
    {
        $checks = StreamHealthCheck::query()->when($this->result === 'healthy', fn (Builder $query) => $query->where('was_healthy', true))->when($this->result === 'failed', fn (Builder $query) => $query->where('was_healthy', false))
            ->when($this->q, fn (Builder $query) => $query->whereHas('streamSource.media', fn (Builder $media) => $media->where('name', 'like', '%'.$this->q.'%')))
            ->with(['streamSource.media.country'])->latest('checked_at')->paginate(40);
        $stats = ['checks_24h' => StreamHealthCheck::where('checked_at', '>=', now()->subDay())->count(), 'healthy_24h' => StreamHealthCheck::where('checked_at', '>=', now()->subDay())->where('was_healthy', true)->count(), 'failed_24h' => StreamHealthCheck::where('checked_at', '>=', now()->subDay())->where('was_healthy', false)->count(), 'average_response_ms' => (int) StreamHealthCheck::where('checked_at', '>=', now()->subDay())->where('was_healthy', true)->avg('response_time_ms')];

        return view('livewire.admin.streams.health-history', compact('checks', 'stats'))->layoutData(['title' => 'Health-check history']);
    }
}
