<?php

namespace App\Livewire\Admin\Radio;

use App\Enums\MediaType;
use App\Enums\StreamStatus;
use App\Models\Media;
use App\Services\Media\MediaQualityService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class DataQuality extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $issue = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['q', 'issue'], true)) {
            $this->resetPage();
        }
    }

    public function runCleanup(MediaQualityService $service): void
    {
        $result = $service->clean(false, MediaType::Radio);
        session()->flash('success', "Quality scan complete: {$result['flagged']} media flagged and {$result['genres_removed']} noisy genre links removed. No records were merged.");
    }

    public function render(): View
    {
        $records = Media::query()->where('type', MediaType::Radio)
            ->with(['country', 'radioStation', 'artworks', 'genres', 'languages', 'streamSources'])->get()
            ->map(fn (Media $station) => ['station' => $station, 'issues' => $this->issues($station)])
            ->filter(fn (array $record) => $record['issues'] !== [])
            ->when($this->issue, fn (Collection $records) => $records->filter(fn (array $record) => in_array($this->issue, $record['issues'], true)))
            ->when($this->q, fn (Collection $records) => $records->filter(fn (array $record) => str_contains(mb_strtolower($record['station']->name), mb_strtolower($this->q))))
            ->sortByDesc(fn (array $record) => count($record['issues']))->values();
        $page = $this->getPage();
        $stations = new LengthAwarePaginator($records->forPage($page, 25)->values(), $records->count(), 25, $page, ['path' => request()->url(), 'pageName' => 'page']);
        $counts = collect($records)->flatMap(fn (array $record) => $record['issues'])->countBy()->sortDesc();

        return view('livewire.admin.radio.data-quality', compact('stations', 'counts'))
            ->layoutData(['title' => 'Radio data quality']);
    }

    /** @return list<string> */
    private function issues(Media $station): array
    {
        return collect([
            $station->country_id ? null : 'missing_country',
            $station->artworks->isEmpty() ? 'missing_artwork' : null,
            $station->streamSources->isEmpty() ? 'missing_stream' : null,
            $station->streamSources->isNotEmpty() && $station->streamSources->every(fn ($stream) => $stream->status === StreamStatus::Offline) ? 'all_streams_offline' : null,
            $station->languages->isEmpty() ? 'missing_language' : null,
            $station->genres->isEmpty() ? 'missing_genre' : null,
            trim((string) $station->description) === '' ? 'missing_description' : null,
            mb_strlen($station->name) > 180 ? 'long_name' : null,
            preg_match('/https?:\/\/|\.(com|net|org)\b/i', $station->name) ? 'suspicious_name' : null,
        ])->filter()->values()->all();
    }
}
