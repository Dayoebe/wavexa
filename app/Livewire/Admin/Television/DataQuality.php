<?php

namespace App\Livewire\Admin\Television;

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

    #[Url(except: '')] public string $q = '';
    #[Url(except: '')] public string $issue = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['q', 'issue'], true)) $this->resetPage();
    }

    public function runCleanup(MediaQualityService $service): void
    {
        $result = $service->clean(false, MediaType::Television);
        session()->flash('success', "Quality scan complete: {$result['flagged']} television records flagged and {$result['genres_removed']} noisy genre links removed. No records were merged.");
    }

    public function render(): View
    {
        $records = Media::query()->where('type', MediaType::Television)->with(['country', 'artworks', 'genres', 'languages', 'streamSources'])->get()
            ->map(fn (Media $channel) => ['channel' => $channel, 'issues' => $this->issues($channel)])->filter(fn (array $record) => $record['issues'] !== [])
            ->when($this->issue, fn (Collection $records) => $records->filter(fn (array $record) => in_array($this->issue, $record['issues'], true)))
            ->when($this->q, fn (Collection $records) => $records->filter(fn (array $record) => str_contains(mb_strtolower($record['channel']->name), mb_strtolower($this->q))))
            ->sortByDesc(fn (array $record) => count($record['issues']))->values();
        $page = $this->getPage(); $channels = new LengthAwarePaginator($records->forPage($page, 25)->values(), $records->count(), 25, $page, ['path' => request()->url(), 'pageName' => 'page']);
        $counts = collect($records)->flatMap(fn (array $record) => $record['issues'])->countBy()->sortDesc();
        return view('livewire.admin.television.data-quality', compact('channels', 'counts'))->layoutData(['title' => 'Television data quality']);
    }

    /** @return list<string> */
    private function issues(Media $channel): array
    {
        return collect([$channel->country_id ? null : 'missing_country', $channel->artworks->isEmpty() ? 'missing_artwork' : null, $channel->streamSources->isEmpty() ? 'missing_stream' : null,
            $channel->streamSources->isNotEmpty() && $channel->streamSources->every(fn ($stream) => $stream->status === StreamStatus::Offline) ? 'all_streams_offline' : null,
            $channel->languages->isEmpty() ? 'missing_language' : null, $channel->genres->isEmpty() ? 'missing_genre' : null, trim((string) $channel->description) === '' ? 'missing_description' : null,
            mb_strlen($channel->name) > 180 ? 'long_name' : null, preg_match('/https?:\/\/|\.(com|net|org)\b/i', $channel->name) ? 'suspicious_name' : null])->filter()->values()->all();
    }
}
