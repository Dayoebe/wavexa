<?php

namespace App\Livewire\Admin\Ingestion;

use App\Jobs\RunIngestion;
use App\Models\IngestionRun;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class History extends Component
{
    use WithPagination;

    #[Url(except: 'all')]
    public string $type = 'all';

    #[Url(except: 'all')]
    public string $status = 'all';

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function retry(int $runId): void
    {
        $original = IngestionRun::query()->with('sourceProvider')->findOrFail($runId);
        if ($original->sourceProvider && ! $original->sourceProvider->is_active) {
            $this->addError('source', 'This source provider is disabled. Enable it before running the import again.');

            return;
        }
        $run = IngestionRun::query()->create(['source_provider_id' => $original->source_provider_id, 'requested_by' => auth()->id(), 'type' => $original->type, 'status' => 'queued', 'options' => $original->options]);
        RunIngestion::dispatch($run->id);
        session()->flash('status', 'Import #'.$run->id.' queued using the previous options.');
    }

    public function render(): View
    {
        $runs = IngestionRun::query()->when($this->type !== 'all', fn (Builder $query) => $query->where('type', $this->type))->when($this->status !== 'all', fn (Builder $query) => $query->where('status', $this->status))->with(['sourceProvider', 'requestedBy'])->latest()->paginate(30);
        $stats = ['queued' => IngestionRun::where('status', 'queued')->count(), 'running' => IngestionRun::where('status', 'running')->count(), 'completed' => IngestionRun::where('status', 'completed')->count(), 'failed' => IngestionRun::where('status', 'failed')->count()];

        return view('livewire.admin.ingestion.history', compact('runs', 'stats'))->layoutData(['title' => 'Import history']);
    }
}
