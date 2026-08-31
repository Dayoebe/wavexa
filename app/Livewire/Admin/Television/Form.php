<?php

namespace App\Livewire\Admin\Television;

use App\Enums\ArtworkKind;
use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Enums\StreamStatus;
use App\Enums\VerificationStatus;
use App\Models\AdministrativeArea;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Media;
use App\Models\SourceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class Form extends Component
{
    public ?int $channelId = null;
    public string $name = '';
    public string $slug = '';
    public string $status = 'draft';
    public string $description = '';
    public string $websiteUrl = '';
    public string $countryId = '';
    public string $administrativeAreaId = '';
    public string $cityId = '';
    public string $callSign = '';
    public string $artworkUrl = '';
    /** @var list<int|string> */ public array $categoryIds = [];
    /** @var list<int|string> */ public array $genreIds = [];
    /** @var list<int|string> */ public array $languageIds = [];
    public string $primaryLanguageId = '';
    /** @var list<array<string, mixed>> */ public array $streams = [];

    public function mount(?Media $channel = null): void
    {
        if (! $channel?->exists) { $this->addStream(); return; }
        abort_unless($channel->type === MediaType::Television && ! $channel->trashed(), 404);
        $channel->load(['tvChannel', 'categories', 'genres', 'languages', 'artworks', 'streamSources']);
        $this->channelId = $channel->id; $this->name = $channel->name; $this->slug = $channel->slug; $this->status = $channel->status->value;
        $this->description = $channel->description ?? ''; $this->websiteUrl = $channel->website_url ?? ''; $this->countryId = (string) ($channel->country_id ?? '');
        $this->administrativeAreaId = (string) ($channel->administrative_area_id ?? ''); $this->cityId = (string) ($channel->city_id ?? ''); $this->callSign = $channel->tvChannel?->call_sign ?? '';
        $this->artworkUrl = $channel->artworks->firstWhere('is_primary', true)?->url ?? ''; $this->categoryIds = $channel->categories->modelKeys(); $this->genreIds = $channel->genres->modelKeys(); $this->languageIds = $channel->languages->modelKeys();
        $this->primaryLanguageId = (string) ($channel->languages->firstWhere('pivot.is_primary', true)?->id ?? '');
        $this->streams = $channel->streamSources->map(fn ($stream) => ['id' => $stream->id, 'url' => $stream->url, 'resolved_url' => $stream->resolved_url ?? '', 'format' => $stream->format, 'codec' => $stream->codec ?? '', 'bitrate_kbps' => $stream->bitrate_kbps ?? '', 'source_provider_id' => $stream->source_provider_id ?? '', 'status' => $stream->status->value, 'verification_status' => $stream->verification_status->value, 'is_primary' => $stream->is_primary])->values()->all();
        if ($this->streams === []) $this->addStream();
    }

    public function updatedName(string $name): void
    {
        if (! $this->channelId || $this->slug === '') $this->slug = Str::slug($name);
    }

    public function updatedCountryId(): void { $this->reset(['administrativeAreaId', 'cityId']); }
    public function updatedAdministrativeAreaId(): void { $this->cityId = ''; }
    public function addStream(): void { $this->streams[] = ['id' => null, 'url' => '', 'resolved_url' => '', 'format' => 'hls', 'codec' => '', 'bitrate_kbps' => '', 'source_provider_id' => '', 'status' => 'unknown', 'verification_status' => 'unverified', 'is_primary' => count($this->streams) === 0]; }
    public function removeStream(int $index): void { unset($this->streams[$index]); $this->streams = array_values($this->streams); if ($this->streams !== [] && ! collect($this->streams)->contains('is_primary', true)) $this->streams[0]['is_primary'] = true; }
    public function makePrimary(int $index): void { foreach ($this->streams as $key => $stream) $this->streams[$key]['is_primary'] = $key === $index; }

    public function save(): void
    {
        $validated = $this->validate($this->rules());
        $channel = DB::transaction(function () use ($validated): Media {
            $channel = $this->channelId ? Media::query()->where('type', MediaType::Television)->findOrFail($this->channelId) : new Media(['type' => MediaType::Television]);
            $channel->fill(['status' => $validated['status'], 'name' => $validated['name'], 'slug' => $validated['slug'], 'description' => $validated['description'] ?: null, 'website_url' => $validated['websiteUrl'] ?: null, 'country_id' => $validated['countryId'] ?: null, 'administrative_area_id' => $validated['administrativeAreaId'] ?: null, 'city_id' => $validated['cityId'] ?: null])->save();
            $channel->tvChannel()->updateOrCreate([], ['call_sign' => $validated['callSign'] ?: null]);
            $channel->categories()->sync($validated['categoryIds'] ?? []); $channel->genres()->sync($validated['genreIds'] ?? []);
            $channel->languages()->sync(collect($validated['languageIds'] ?? [])->mapWithKeys(fn ($id) => [$id => ['is_primary' => (string) $id === (string) $validated['primaryLanguageId']]])->all());
            $channel->artworks()->where('is_primary', true)->delete();
            if ($validated['artworkUrl']) $channel->artworks()->create(['kind' => ArtworkKind::Logo, 'url' => $validated['artworkUrl'], 'is_primary' => true]);
            $retained = [];
            foreach ($validated['streams'] ?? [] as $index => $stream) {
                $url = trim($stream['url']); $record = isset($stream['id']) ? $channel->streamSources()->find($stream['id']) : null; $record ??= $channel->streamSources()->make();
                $record->fill(['url' => $url, 'resolved_url' => $stream['resolved_url'] ?: null, 'url_hash' => hash('sha256', $url), 'protocol' => parse_url($url, PHP_URL_SCHEME) ?: 'https', 'format' => strtolower($stream['format']), 'codec' => $stream['codec'] ?: null, 'bitrate_kbps' => $stream['bitrate_kbps'] ?: null, 'source_provider_id' => $stream['source_provider_id'] ?: null, 'status' => $stream['status'], 'verification_status' => $stream['verification_status'], 'is_primary' => (bool) ($stream['is_primary'] ?? $index === 0)])->save(); $retained[] = $record->id;
            }
            $channel->streamSources()->whereNotIn('id', $retained)->delete(); return $channel;
        });
        session()->flash('success', $this->channelId ? 'Television channel updated.' : 'Television channel created.'); $this->redirectRoute('admin.television.show', $channel, navigate: true);
    }

    public function render(): View
    {
        $countries = Country::query()->orderBy('name')->get(['id', 'name']); $areas = AdministrativeArea::query()->when($this->countryId, fn ($query) => $query->where('country_id', $this->countryId))->orderBy('name')->get(['id', 'name']);
        $cities = City::query()->when($this->countryId, fn ($query) => $query->where('country_id', $this->countryId))->when($this->administrativeAreaId, fn ($query) => $query->where('administrative_area_id', $this->administrativeAreaId))->orderBy('name')->get(['id', 'name']);
        $categories = Category::query()->orderBy('name')->get(['id', 'name']); $genres = Genre::query()->orderBy('name')->get(['id', 'name']); $languages = Language::query()->orderBy('name')->get(['id', 'name']); $providers = SourceProvider::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        return view('livewire.admin.television.form', compact('countries', 'areas', 'cities', 'categories', 'genres', 'languages', 'providers'))->layoutData(['title' => $this->channelId ? 'Edit television channel' : 'Add television channel']);
    }

    private function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('media', 'slug')->where('type', MediaType::Television->value)->ignore($this->channelId)], 'status' => ['required', Rule::enum(MediaStatus::class)], 'description' => ['nullable', 'string', 'max:10000'], 'websiteUrl' => ['nullable', 'url', 'max:2048'], 'countryId' => ['nullable', 'integer', 'exists:countries,id'], 'administrativeAreaId' => ['nullable', 'integer', 'exists:administrative_areas,id'], 'cityId' => ['nullable', 'integer', 'exists:cities,id'], 'callSign' => ['nullable', 'string', 'max:255'], 'artworkUrl' => ['nullable', 'url', 'max:2048'], 'categoryIds' => ['array'], 'categoryIds.*' => ['integer', 'exists:categories,id'], 'genreIds' => ['array'], 'genreIds.*' => ['integer', 'exists:genres,id'], 'languageIds' => ['array'], 'languageIds.*' => ['integer', 'exists:languages,id'], 'primaryLanguageId' => ['nullable', 'integer', 'exists:languages,id'], 'streams' => ['array'], 'streams.*.id' => ['nullable', 'integer'], 'streams.*.url' => ['required', 'url', 'max:4096'], 'streams.*.resolved_url' => ['nullable', 'url', 'max:4096'], 'streams.*.format' => ['required', 'string', 'max:32'], 'streams.*.codec' => ['nullable', 'string', 'max:32'], 'streams.*.bitrate_kbps' => ['nullable', 'integer', 'min:1', 'max:100000'], 'streams.*.source_provider_id' => ['nullable', 'integer', 'exists:source_providers,id'], 'streams.*.status' => ['required', Rule::enum(StreamStatus::class)], 'streams.*.verification_status' => ['required', Rule::enum(VerificationStatus::class)], 'streams.*.is_primary' => ['boolean']];
    }
}
