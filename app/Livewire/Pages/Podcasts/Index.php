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

    #[Url(except: 'all')]
    public string $format = 'all';

    public function mount(?string $format = null): void
    {
        if (in_array($format, ['audio', 'video'], true)) {
            $this->format = $format;
        }
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedFormat(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $podcasts = Media::query()->where('type', MediaType::Podcast)->where('status', MediaStatus::Published)
            ->whereHas('podcast')->with(['podcast', 'country', 'artworks'])
            ->withCount(['podcastEpisodes as episode_count'])
            ->withExists(['podcastEpisodes as has_video' => fn (Builder $query) => $query->whereHas('media.primaryStream', fn (Builder $stream) => $stream->whereIn('format', ['mp4', 'webm']))])
            ->when($this->format === 'video', fn (Builder $query) => $query->whereHas('podcastEpisodes.media.primaryStream', fn (Builder $stream) => $stream->whereIn('format', ['mp4', 'webm'])))
            ->when($this->format === 'audio', fn (Builder $query) => $query->whereDoesntHave('podcastEpisodes.media.primaryStream', fn (Builder $stream) => $stream->whereIn('format', ['mp4', 'webm'])))
            ->when($this->q, fn (Builder $query) => $query->where(function (Builder $query): void {
                $query->where('name', 'like', '%'.$this->q.'%')->orWhere('description', 'like', '%'.$this->q.'%')
                    ->orWhereHas('podcast', fn (Builder $podcast) => $podcast->where('author', 'like', '%'.$this->q.'%'));
            }))->latest('updated_at')->paginate(18);

        $title = match ($this->format) {
            'audio' => 'Audio Podcasts — Wavexa',
            'video' => 'Video Podcasts — Wavexa',
            default => 'Discover Podcasts — Wavexa',
        };
        $description = match ($this->format) {
            'audio' => 'Discover audio podcasts and listen directly from publisher RSS feeds.',
            'video' => 'Discover video podcasts and watch publisher-hosted episodes on Wavexa.',
            default => 'Discover podcasts and play recent episodes directly from publisher RSS feeds.',
        };
        $canonical = route(match ($this->format) {
            'audio' => 'podcasts.audio', 'video' => 'podcasts.video', default => 'podcasts.index',
        });

        return view('livewire.pages.podcasts.index', compact('podcasts'))
            ->layoutData(compact('title', 'description', 'canonical') + ['structuredData' => Seo::schema($title, $description, $canonical)]);
    }
}
