<?php

namespace App\Livewire\Admin\Podcasts;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
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

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $podcasts = Media::query()->where('type', MediaType::Podcast)->with(['podcast', 'country', 'artworks'])
            ->withCount('podcastEpisodes')
            ->when($this->q, fn (Builder $query) => $query->where(function (Builder $query): void {
                $query->where('name', 'like', '%'.$this->q.'%')->orWhereHas('podcast', fn (Builder $podcast) => $podcast->where('author', 'like', '%'.$this->q.'%'));
            }))->latest('updated_at')->paginate(25);
        $stats = [
            'podcasts' => Media::where('type', MediaType::Podcast)->count(),
            'published' => Media::where('type', MediaType::Podcast)->where('status', MediaStatus::Published)->count(),
            'episodes' => Media::where('type', MediaType::PodcastEpisode)->count(),
        ];

        return view('livewire.admin.podcasts.index', compact('podcasts', 'stats'))->layoutData(['title' => 'Podcast catalogue']);
    }
}
