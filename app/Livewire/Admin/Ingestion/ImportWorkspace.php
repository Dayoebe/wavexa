<?php

namespace App\Livewire\Admin\Ingestion;

use App\Jobs\RunIngestion;
use App\Models\IngestionRun;
use App\Models\SourceProvider;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class ImportWorkspace extends Component
{
    public string $type = 'radio';

    public string $country = '';

    public string $term = 'Nigeria';

    public string $name = '';

    public string $tag = '';

    public string $language = '';

    public int $limit = 100;

    public int $offset = 0;

    public int $episodes = 25;

    public function mount(?string $type = null): void
    {
        abort_unless(in_array($type, ['radio', 'tv', 'podcast'], true), 404);
        $this->type = $type;
        $this->country = $type === 'podcast' ? 'NG' : '';
        $this->limit = match ($type) {
            'tv' => 500, 'podcast' => 25, default => 100
        };
    }

    public function queueImport(): void
    {
        $rules = ['country' => ['nullable', 'string', 'size:2'], 'limit' => ['required', 'integer', 'min:1', 'max:'.($this->type === 'tv' ? 5000 : ($this->type === 'podcast' ? 200 : 500))]];
        if ($this->type === 'radio') {
            $rules += ['name' => ['nullable', 'string', 'max:120'], 'tag' => ['nullable', 'string', 'max:120'], 'language' => ['nullable', 'string', 'max:120'], 'offset' => ['required', 'integer', 'min:0']];
        }
        if ($this->type === 'podcast') {
            $rules += ['term' => ['required', 'string', 'max:120'], 'country' => ['required', 'string', 'size:2'], 'episodes' => ['required', 'integer', 'min:1', 'max:100']];
        }
        $this->validate($rules);

        $provider = SourceProvider::query()->firstOrCreate(['slug' => $this->providerSlug()], $this->providerDefaults());
        if (! $provider->is_active) {
            $this->addError('source', 'This source provider is disabled. Enable it before starting an import.');

            return;
        }
        $run = IngestionRun::query()->create(['source_provider_id' => $provider->id, 'requested_by' => auth()->id(), 'type' => $this->type, 'status' => 'queued', 'options' => $this->options()]);
        RunIngestion::dispatch($run->id);
        session()->flash('status', 'Import #'.$run->id.' queued. A queue worker will process it in the background.');
    }

    private function options(): array
    {
        return match ($this->type) {
            'radio' => array_filter(['country' => strtoupper($this->country) ?: null, 'name' => $this->name ?: null, 'tag' => $this->tag ?: null, 'language' => $this->language ?: null, 'limit' => $this->limit, 'offset' => $this->offset], fn ($value) => $value !== null && $value !== ''),
            'tv' => ['country' => strtoupper($this->country) ?: null, 'limit' => $this->limit],
            default => ['term' => $this->term, 'country' => strtoupper($this->country), 'limit' => $this->limit, 'episodes' => $this->episodes],
        };
    }

    private function providerSlug(): string
    {
        return match ($this->type) {
            'radio' => 'radio-browser', 'tv' => 'free-tv', default => 'apple-podcasts'
        };
    }

    private function providerDefaults(): array
    {
        return match ($this->type) {
            'radio' => ['name' => 'Radio Browser', 'website_url' => 'https://www.radio-browser.info/', 'is_active' => true],
            'tv' => ['name' => 'Free-TV', 'website_url' => 'https://github.com/Free-TV/IPTV', 'is_active' => true],
            default => ['name' => 'Apple Podcasts Directory', 'website_url' => 'https://podcasts.apple.com', 'is_active' => true],
        };
    }

    public function render(): View
    {
        $runs = IngestionRun::query()->where('type', $this->type)->with('requestedBy')->latest()->limit(8)->get();

        return view('livewire.admin.ingestion.import-workspace', compact('runs'))->layoutData(['title' => match ($this->type) {
            'tv' => 'TV imports', 'podcast' => 'Podcast imports', default => 'Radio imports'
        }]);
    }
}
