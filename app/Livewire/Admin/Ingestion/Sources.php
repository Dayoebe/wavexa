<?php

namespace App\Livewire\Admin\Ingestion;

use App\Models\SourceProvider;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class Sources extends Component
{
    public function toggle(int $providerId): void
    {
        $provider = SourceProvider::query()->findOrFail($providerId);
        $provider->update(['is_active' => ! $provider->is_active]);
    }

    public function render(): View
    {
        $providers = SourceProvider::query()->withCount(['mediaSources', 'streamSources', 'ingestionRuns'])->orderBy('name')->get();

        return view('livewire.admin.ingestion.sources', compact('providers'))->layoutData(['title' => 'Source providers']);
    }
}
