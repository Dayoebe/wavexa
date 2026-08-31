<?php

namespace App\Livewire\Admin\Television;

use App\Enums\MediaType;
use App\Models\Media;
use App\Services\Media\MediaQualityService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class Duplicates extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    /** @var array<string, int> */
    public array $survivors = [];

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function merge(string $signature, MediaQualityService $service): void
    {
        $group = $this->groups($service)->firstWhere('signature', $signature);
        abort_unless($group, 404);
        $targetId = (int) ($this->survivors[$signature] ?? $group['channels']->first()->id);
        $merged = $service->mergeTelevisionGroup($group['channels']->pluck('id')->all(), $targetId);
        unset($this->survivors[$signature]);
        session()->flash('success', $merged.' duplicate channel'.($merged === 1 ? '' : 's').' merged.');
        $this->resetPage();
    }

    public function render(MediaQualityService $service): View
    {
        $groups = $this->groups($service);
        $page = $this->getPage();
        $duplicates = new LengthAwarePaginator($groups->forPage($page, 12)->values(), $groups->count(), 12, $page, ['path' => request()->url(), 'pageName' => 'page']);

        return view('livewire.admin.television.duplicates', compact('duplicates'))->layoutData(['title' => 'Television duplicates']);
    }

    private function groups(MediaQualityService $service): Collection
    {
        return Media::query()->where('type', MediaType::Television)->with(['country', 'tvChannel', 'streamSources', 'sources.sourceProvider'])->get()
            ->groupBy(fn (Media $channel) => $service->duplicateSignature($channel))->filter(fn (Collection $channels) => $channels->count() > 1)
            ->map(fn (Collection $channels, string $signature) => ['signature' => $signature, 'channels' => $channels->sortBy('id')->values()])
            ->when($this->q, fn (Collection $groups) => $groups->filter(fn (array $group) => str_contains(mb_strtolower($group['channels']->first()->name), mb_strtolower($this->q))))
            ->sortBy(fn (array $group) => $group['channels']->first()->name)->values();
    }
}
