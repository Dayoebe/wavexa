<?php

namespace App\Livewire\Pages;

use App\Models\UserFavorite;
use App\Models\UserPlaybackHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Library extends Component
{
    use WithPagination;

    public string $tab = 'favorites';

    public function mount(): void
    {
        $this->tab = request()->query('tab') === 'history' ? 'history' : 'favorites';
    }

    public function showTab(string $tab): void
    {
        $this->tab = in_array($tab, ['favorites', 'history'], true) ? $tab : 'favorites';
        $this->resetPage();
    }

    public function removeFavorite(int $mediaId): void
    {
        Auth::user()->favoriteMedia()->detach($mediaId);
    }

    public function clearHistory(): void
    {
        Auth::user()->playbackHistory()->delete();
    }

    public function render(): View
    {
        $query = $this->tab === 'history'
            ? UserPlaybackHistory::query()->where('user_id', Auth::id())->whereHas('media', fn ($query) => $query->where('status', 'published'))->with(['media.country', 'media.artworks', 'media.primaryStream', 'media.podcastEpisode.podcast.media'])->orderByDesc('last_played_at')
            : UserFavorite::query()->where('user_id', Auth::id())->whereHas('media', fn ($query) => $query->where('status', 'published'))->with(['media.country', 'media.artworks', 'media.primaryStream', 'media.podcastEpisode.podcast.media'])->latest();

        return view('livewire.pages.library', ['items' => $query->paginate(18)])
            ->layoutData(['title' => 'Your library — Wavexa', 'description' => 'Your private saved and recently played Wavexa media.', 'robots' => 'noindex, nofollow']);
    }
}
