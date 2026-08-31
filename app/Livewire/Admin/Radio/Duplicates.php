<?php

namespace App\Livewire\Admin\Radio;

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
        $targetId = (int) ($this->survivors[$signature] ?? $group['stations']->first()->id);
        $merged = $service->mergeRadioGroup($group['stations']->pluck('id')->all(), $targetId);
        unset($this->survivors[$signature]);
        session()->flash('success', $merged.' duplicate station'.($merged === 1 ? '' : 's').' merged.');
        $this->resetPage();
    }

    public function render(MediaQualityService $service): View
    {
        $groups = $this->groups($service);
        $page = $this->getPage();
        $duplicates = new LengthAwarePaginator($groups->forPage($page, 12)->values(), $groups->count(), 12, $page, ['path' => request()->url(), 'pageName' => 'page']);

        return view('livewire.admin.radio.duplicates', compact('duplicates'))
            ->layoutData(['title' => 'Radio duplicates']);
    }

    private function groups(MediaQualityService $service): Collection
    {
        return Media::query()->where('type', MediaType::Radio)
            ->with(['country', 'radioStation', 'streamSources', 'sources.sourceProvider'])
            ->get()->groupBy(fn (Media $station) => $service->duplicateSignature($station))
            ->filter(fn (Collection $stations) => $stations->count() > 1)
            ->map(function (Collection $stations, string $signature): array {
                return ['signature' => $signature, 'stations' => $stations->sortBy('id')->values()];
            })->when($this->q, fn (Collection $groups) => $groups->filter(fn (array $group) => str_contains(mb_strtolower($group['stations']->first()->name), mb_strtolower($this->q))))
            ->sortBy(fn (array $group) => $group['stations']->first()->name)->values();
    }
}
