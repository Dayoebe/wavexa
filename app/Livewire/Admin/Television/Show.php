<?php

namespace App\Livewire\Admin\Television;

use App\Enums\MediaType;
use App\Models\Media;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class Show extends Component
{
    public Media $channel;

    public function mount(Media $channel): void
    {
        abort_unless($channel->type === MediaType::Television && ! $channel->trashed(), 404);
        $this->channel = $channel;
    }

    public function render(): View
    {
        $this->channel->load(['country', 'administrativeArea', 'city', 'tvChannel', 'categories', 'genres', 'languages', 'artworks', 'streamSources.sourceProvider', 'streamSources.reports', 'sources.sourceProvider']);

        return view('livewire.admin.television.show')->layoutData(['title' => $this->channel->name]);
    }
}
