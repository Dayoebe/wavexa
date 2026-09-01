<?php

namespace App\Livewire\Admin\Streams;

use App\Jobs\CheckStreamHealth;
use App\Models\StreamReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class BrokenReports extends Component
{
    use WithPagination;

    #[Url(except: 'open')]
    public string $status = 'open';

    #[Url(except: '')]
    public string $q = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function resolve(int $reportId): void
    {
        StreamReport::query()->findOrFail($reportId)->update(['resolved_at' => now()]);
    }

    public function reopen(int $reportId): void
    {
        StreamReport::query()->findOrFail($reportId)->update(['resolved_at' => null]);
    }

    public function recheck(int $reportId): void
    {
        $report = StreamReport::query()->findOrFail($reportId);
        CheckStreamHealth::dispatch($report->stream_source_id);
        session()->flash('status', 'A health check was queued for the reported stream.');
    }

    public function render(): View
    {
        $reports = StreamReport::query()->when($this->status === 'open', fn (Builder $query) => $query->whereNull('resolved_at'))->when($this->status === 'resolved', fn (Builder $query) => $query->whereNotNull('resolved_at'))
            ->when($this->q, fn (Builder $query) => $query->whereHas('streamSource.media', fn (Builder $media) => $media->where('name', 'like', '%'.$this->q.'%')))
            ->with(['streamSource.media.country'])->latest()->paginate(30);
        $counts = ['open' => StreamReport::whereNull('resolved_at')->count(), 'resolved' => StreamReport::whereNotNull('resolved_at')->count()];

        return view('livewire.admin.streams.broken-reports', compact('reports', 'counts'))->layoutData(['title' => 'Broken stream reports']);
    }
}
