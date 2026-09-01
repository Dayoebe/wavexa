<?php

namespace App\Livewire\Pages\Podcasts;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Models\Media;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
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
        $podcasts = Media::query()->where('type', MediaType::Podcast)->where('status', MediaStatus::Published)
            ->whereHas('podcast')->with(['podcast', 'country', 'artworks'])
            ->withCount(['podcastEpisodes as episode_count'])
            ->when($this->q, fn (Builder $query) => $query->where(function (Builder $query): void {
                $query->where('name', 'like', '%'.$this->q.'%')->orWhere('description', 'like', '%'.$this->q.'%')
                    ->orWhereHas('podcast', fn (Builder $podcast) => $podcast->where('author', 'like', '%'.$this->q.'%'));
            }))->latest('updated_at')->paginate(18);

        $title = 'Discover Podcasts — Wavexa';
        $description = 'Discover podcasts and play recent episodes directly from publisher RSS feeds.';
        $canonical = route('podcasts.index');

        return view('livewire.pages.podcasts.index', compact('podcasts'))
            ->layoutData(compact('title', 'description', 'canonical') + ['structuredData' => Seo::schema($title, $description, $canonical)]);
    }
}
