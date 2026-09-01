<?php

namespace App\Livewire\Admin\Podcasts;

use App\Models\Country;
use App\Models\Podcast;
use App\Services\Podcasts\PodcastImporter;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.dashboard')]
class Form extends Component
{
    public string $feedUrl = '';

    public string $country = 'NG';

    public int $episodeLimit = 25;

    public function save(PodcastImporter $importer): mixed
    {
        $validated = $this->validate([
            'feedUrl' => ['required', 'url:http,https', 'max:2048'],
            'country' => ['required', 'string', 'size:2', 'exists:countries,iso_alpha_2'],
            'episodeLimit' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        try {
            $importer->importFeed($validated['feedUrl'], strtoupper($validated['country']), [], $validated['episodeLimit']);
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('feedUrl', 'The feed could not be imported. Confirm that it is a public podcast RSS URL and try again.');

            return null;
        }

        $podcast = Podcast::query()->where('feed_url_hash', hash('sha256', $validated['feedUrl']))->firstOrFail();
        session()->flash('success', 'Podcast and recent episodes imported successfully.');

        return $this->redirectRoute('admin.podcasts.index', navigate: true);
    }

    public function render(): View
    {
        $countries = Country::query()->orderByRaw("CASE WHEN iso_alpha_2 = 'NG' THEN 0 ELSE 1 END")->orderBy('name')->get(['name', 'iso_alpha_2']);

        return view('livewire.admin.podcasts.form', compact('countries'))->layoutData(['title' => 'Add podcast']);
    }
}
