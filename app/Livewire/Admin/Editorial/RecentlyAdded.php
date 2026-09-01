<?php

namespace App\Livewire\Admin\Editorial;

use App\Enums\MediaType;
use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class RecentlyAdded extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: 'all')]
    public string $type = 'all';

    #[Url(except: '30')]
    public string $days = '30';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedDays(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $media = Media::query()->whereIn('type', [MediaType::Radio, MediaType::Television, MediaType::Podcast])
            ->when($this->type !== 'all', fn (Builder $query) => $query->where('type', $this->type))
            ->when($this->days !== 'all', fn (Builder $query) => $query->where('created_at', '>=', now()->subDays((int) $this->days)))
            ->when($this->q, fn (Builder $query) => $query->where('name', 'like', '%'.$this->q.'%'))
            ->with('country')->latest()->paginate(30);

        return view('livewire.admin.editorial.recently-added', compact('media'))->layoutData(['title' => 'Recently added']);
    }
}
