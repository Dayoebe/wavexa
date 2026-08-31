<?php

namespace App\Livewire\Admin\Radio;

use App\Enums\MediaType;
use App\Models\Media;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class Show extends Component
{
    public Media $station;

    public function mount(Media $station): void
    {
        abort_unless($station->type === MediaType::Radio && ! $station->trashed(), 404);
        $this->station = $station;
    }

    public function render(): View
    {
        $this->station->load([
            'country', 'administrativeArea', 'city', 'radioStation', 'categories', 'genres', 'languages',
            'artworks', 'streamSources.sourceProvider', 'streamSources.reports', 'sources.sourceProvider',
        ]);

        return view('livewire.admin.radio.show')->layoutData(['title' => $this->station->name]);
    }
}
