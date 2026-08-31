<?php

namespace App\Livewire\Admin\Television;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Models\Country;
use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $country = '';

    #[Url(except: 'active')]
    public string $records = 'active';

    public function updated(string $property): void
    {
        if (in_array($property, ['q', 'status', 'country', 'records'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['q', 'status', 'country']);
        $this->records = 'active';
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->channel($id)->delete();
        session()->flash('success', 'Channel moved to deleted records.');
    }

    public function restore(int $id): void
    {
        $this->channel($id, true)->restore();
        session()->flash('success', 'Channel restored.');
    }

    public function forceDelete(int $id): void
    {
        $channel = $this->channel($id, true);
        abort_unless($channel->trashed(), 422);
        $channel->forceDelete();
        session()->flash('success', 'Channel permanently deleted.');
    }

    public function render(): View
    {
        $query = Media::query()->where('type', MediaType::Television)->with(['country', 'tvChannel', 'primaryStream', 'artworks']);
        if ($this->records === 'deleted') {
            $query->onlyTrashed();
        }
        $query->when($this->q, fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search): void {
            $query->where('name', 'like', '%'.$search.'%')->orWhere('slug', 'like', '%'.$search.'%')->orWhereHas('tvChannel', fn (Builder $tv) => $tv->where('call_sign', 'like', '%'.$search.'%'));
        }))->when($this->status, fn (Builder $query, string $status) => $query->where('status', $status))->when($this->country, fn (Builder $query, string $country) => $query->where('country_id', $country));
        $channels = $query->latest('updated_at')->paginate(25);
        $countries = Country::query()
            ->whereHas('media', fn (Builder $query) => $query->where('type', MediaType::Television))
            ->withCount(['media as tv_count' => fn (Builder $query) => $query->where('type', MediaType::Television)])
            ->orderByRaw("CASE WHEN iso_alpha_2 = 'NG' THEN 0 ELSE 1 END")
            ->orderBy('name')->get(['id', 'name', 'iso_alpha_2']);
        $stats = ['total' => Media::withTrashed()->where('type', MediaType::Television)->count(), 'published' => Media::where('type', MediaType::Television)->where('status', MediaStatus::Published)->count(), 'draft' => Media::where('type', MediaType::Television)->where('status', MediaStatus::Draft)->count(), 'deleted' => Media::onlyTrashed()->where('type', MediaType::Television)->count()];

        return view('livewire.admin.television.index', compact('channels', 'countries', 'stats'))->layoutData(['title' => 'Television catalogue']);
    }

    private function channel(int $id, bool $withTrashed = false): Media
    {
        return Media::query()->when($withTrashed, fn (Builder $query) => $query->withTrashed())->where('type', MediaType::Television)->findOrFail($id);
    }
}
