<?php

namespace App\Livewire\Admin\Podcasts;

use App\Enums\MediaType;
use App\Models\Media;
use App\Models\Podcast;
use App\Services\Podcasts\PodcastImporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

#[Layout('layouts.dashboard')]
class Feeds extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function sync(int $id, PodcastImporter $importer): void
    {
        $media = Media::query()->where('type', MediaType::Podcast)->with(['podcast', 'country'])->findOrFail($id);
        try {
            $importer->importFeed($media->podcast->feed_url, $media->country?->iso_alpha_2, [], 50);
            session()->flash('success', $media->name.' synchronized successfully.');
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('sync', 'The publisher feed could not be synchronized. Please try again later.');
        }
    }

    public function render(): View
    {
        $feeds = Media::query()->where('type', MediaType::Podcast)->whereHas('podcast', fn (Builder $query) => $query->whereNotNull('feed_url'))
            ->with(['podcast', 'country'])->withCount('podcastEpisodes')
            ->when($this->q, fn (Builder $query) => $query->where('name', 'like', '%'.$this->q.'%'))
            ->orderByDesc(Podcast::query()->select('last_fetched_at')->whereColumn('podcasts.media_id', 'media.id'))->paginate(25);

        return view('livewire.admin.podcasts.feeds', compact('feeds'))->layoutData(['title' => 'Podcast RSS feeds']);
    }
}
