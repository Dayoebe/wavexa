<?php

namespace App\Livewire\Admin\Podcasts;

use App\Enums\MediaType;
use App\Models\Media;
use App\Models\PodcastEpisode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class Episodes extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: 'all')]
    public string $format = 'all';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        Media::query()->where('type', MediaType::PodcastEpisode)->findOrFail($id)->delete();
        session()->flash('success', 'Episode moved to deleted records.');
    }

    public function render(): View
    {
        $episodes = Media::query()->where('type', MediaType::PodcastEpisode)
            ->with(['podcastEpisode.podcast.media', 'primaryStream'])
            ->when($this->q, fn (Builder $query) => $query->where('name', 'like', '%'.$this->q.'%'))
            ->when($this->format === 'video', fn (Builder $query) => $query->whereHas('primaryStream', fn (Builder $stream) => $stream->whereIn('format', ['mp4', 'webm'])))
            ->when($this->format === 'audio', fn (Builder $query) => $query->whereHas('primaryStream', fn (Builder $stream) => $stream->whereNotIn('format', ['mp4', 'webm'])))
            ->orderByDesc(PodcastEpisode::query()->select('published_at')->whereColumn('podcast_episodes.media_id', 'media.id'))->paginate(30);

        return view('livewire.admin.podcasts.episodes', compact('episodes'))->layoutData(['title' => 'Podcast episodes']);
    }
}
