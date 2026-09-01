<?php

namespace App\Livewire\Pages\Podcasts;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Models\Media;
use App\Models\PodcastEpisode;
use App\Support\Seo;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Show extends Component
{
    use WithPagination;

    public Media $podcast;

    public function mount(string $slug): void
    {
        $this->podcast = Media::query()->where('type', MediaType::Podcast)->where('status', MediaStatus::Published)
            ->where('slug', $slug)->with(['podcast', 'country', 'artworks'])->firstOrFail();
    }

    public function render(): View
    {
        $episodes = Media::query()->where('type', MediaType::PodcastEpisode)->where('status', MediaStatus::Published)
            ->whereHas('podcastEpisode', fn ($query) => $query->where('podcast_id', $this->podcast->id))
            ->with(['podcastEpisode', 'primaryStream', 'artworks'])
            ->orderByDesc(PodcastEpisode::query()->select('published_at')->whereColumn('podcast_episodes.media_id', 'media.id'))
            ->paginate(20);
        $title = $this->podcast->name.' — Wavexa Podcasts';
        $description = $this->podcast->description ?: 'Listen to recent episodes of '.$this->podcast->name.'.';
        $canonical = route('podcasts.show', $this->podcast->slug);

        return view('livewire.pages.podcasts.show', compact('episodes'))
            ->layoutData(compact('title', 'description', 'canonical') + ['structuredData' => Seo::schema($title, $description, $canonical)]);
    }
}
