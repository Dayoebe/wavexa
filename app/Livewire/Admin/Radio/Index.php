<?php

namespace App\Livewire\Admin\Radio;

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

    public function setStatus(int $id, string $status): void
    {
        abort_unless(in_array($status, array_column(MediaStatus::cases(), 'value'), true), 422);
        $this->radio($id)->update(['status' => $status]);
        session()->flash('success', 'Station status updated.');
    }

    public function delete(int $id): void
    {
        $this->radio($id)->delete();
        session()->flash('success', 'Station moved to deleted records.');
    }

    public function restore(int $id): void
    {
        $this->radio($id, true)->restore();
        session()->flash('success', 'Station restored.');
    }

    public function forceDelete(int $id): void
    {
        $station = $this->radio($id, true);
        abort_unless($station->trashed(), 422);
        $station->forceDelete();
        session()->flash('success', 'Station permanently deleted.');
    }

    public function render(): View
    {
        $query = Media::query()->where('type', MediaType::Radio)
            ->with(['country', 'radioStation', 'primaryStream', 'artworks']);

        if ($this->records === 'deleted') {
            $query->onlyTrashed();
        }

        $query->when($this->q, fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search): void {
            $query->where('name', 'like', '%'.$search.'%')
                ->orWhere('slug', 'like', '%'.$search.'%')
                ->orWhereHas('radioStation', fn (Builder $radio) => $radio->where('call_sign', 'like', '%'.$search.'%'));
        }))->when($this->status, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($this->country, fn (Builder $query, string $country) => $query->where('country_id', $country));

        $stations = $query->latest('updated_at')->paginate(25);
        $countries = Country::query()->whereHas('media', fn (Builder $query) => $query->where('type', MediaType::Radio))->orderBy('name')->get(['id', 'name']);
        $stats = [
            'total' => Media::withTrashed()->where('type', MediaType::Radio)->count(),
            'published' => Media::where('type', MediaType::Radio)->where('status', MediaStatus::Published)->count(),
            'draft' => Media::where('type', MediaType::Radio)->where('status', MediaStatus::Draft)->count(),
            'deleted' => Media::onlyTrashed()->where('type', MediaType::Radio)->count(),
        ];

        return view('livewire.admin.radio.index', compact('stations', 'countries', 'stats'))
            ->layoutData(['title' => 'Radio catalogue']);
    }

    private function radio(int $id, bool $withTrashed = false): Media
    {
        return Media::query()->when($withTrashed, fn (Builder $query) => $query->withTrashed())
            ->where('type', MediaType::Radio)->findOrFail($id);
    }
}
