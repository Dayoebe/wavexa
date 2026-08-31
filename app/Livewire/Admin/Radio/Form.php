<?php

namespace App\Livewire\Admin\Radio;

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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class Form extends Component
{
    public ?int $stationId = null;

    public string $name = '';

    public string $slug = '';

    public string $status = 'draft';

    public string $description = '';

    public string $websiteUrl = '';

    public string $countryId = '';

    public string $administrativeAreaId = '';

    public string $cityId = '';

    public string $callSign = '';

    public string $frequency = '';

    public string $frequencyUnit = '';

    public string $sourceState = '';

    public string $latitude = '';

    public string $longitude = '';

    public string $artworkUrl = '';

    /** @var list<int|string> */
    public array $categoryIds = [];

    /** @var list<int|string> */
    public array $genreIds = [];

    /** @var list<int|string> */
    public array $languageIds = [];

    public string $primaryLanguageId = '';

    /** @var list<array<string, mixed>> */
    public array $streams = [];

    public function mount(?Media $station = null): void
    {
        if (! $station?->exists) {
            $this->addStream();

            return;
        }

        abort_unless($station->type === MediaType::Radio && ! $station->trashed(), 404);
        $station->load(['radioStation', 'categories', 'genres', 'languages', 'artworks', 'streamSources']);
        $this->stationId = $station->id;
        $this->name = $station->name;
        $this->slug = $station->slug;
        $this->status = $station->status->value;
        $this->description = $station->description ?? '';
        $this->websiteUrl = $station->website_url ?? '';
        $this->countryId = (string) ($station->country_id ?? '');
        $this->administrativeAreaId = (string) ($station->administrative_area_id ?? '');
        $this->cityId = (string) ($station->city_id ?? '');
        $this->callSign = $station->radioStation?->call_sign ?? '';
        $this->frequency = (string) ($station->radioStation?->frequency ?? '');
        $this->frequencyUnit = $station->radioStation?->frequency_unit ?? '';
        $this->sourceState = $station->radioStation?->source_state ?? '';
        $this->latitude = (string) ($station->radioStation?->latitude ?? '');
        $this->longitude = (string) ($station->radioStation?->longitude ?? '');
        $this->artworkUrl = $station->artworks->firstWhere('is_primary', true)?->url ?? '';
        $this->categoryIds = $station->categories->modelKeys();
        $this->genreIds = $station->genres->modelKeys();
        $this->languageIds = $station->languages->modelKeys();
        $this->primaryLanguageId = (string) ($station->languages->firstWhere('pivot.is_primary', true)?->id ?? '');
        $this->streams = $station->streamSources->map(fn ($stream) => [
            'id' => $stream->id, 'url' => $stream->url, 'resolved_url' => $stream->resolved_url ?? '',
            'format' => $stream->format, 'codec' => $stream->codec ?? '', 'bitrate_kbps' => $stream->bitrate_kbps ?? '',
            'source_provider_id' => $stream->source_provider_id ?? '', 'status' => $stream->status->value,
            'verification_status' => $stream->verification_status->value, 'is_primary' => $stream->is_primary,
        ])->values()->all();
        if ($this->streams === []) {
            $this->addStream();
        }
    }

    public function updatedName(string $name): void
    {
        if (! $this->stationId || $this->slug === '') {
            $this->slug = Str::slug($name);
        }
    }

    public function updatedCountryId(): void
    {
        $this->reset(['administrativeAreaId', 'cityId']);
    }

    public function updatedAdministrativeAreaId(): void
    {
        $this->cityId = '';
    }

    public function addStream(): void
    {
        $this->streams[] = ['id' => null, 'url' => '', 'resolved_url' => '', 'format' => 'mp3', 'codec' => '', 'bitrate_kbps' => '', 'source_provider_id' => '', 'status' => 'unknown', 'verification_status' => 'unverified', 'is_primary' => count($this->streams) === 0];
    }

    public function removeStream(int $index): void
    {
        unset($this->streams[$index]);
        $this->streams = array_values($this->streams);
        if ($this->streams !== [] && ! collect($this->streams)->contains('is_primary', true)) {
            $this->streams[0]['is_primary'] = true;
        }
    }

    public function makePrimary(int $index): void
    {
        foreach ($this->streams as $key => $stream) {
            $this->streams[$key]['is_primary'] = $key === $index;
        }
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules());
        if ($this->status === MediaStatus::Published->value && $this->streams === []) {
            throw ValidationException::withMessages(['streams' => 'A published station requires at least one stream.']);
        }

        $station = DB::transaction(function () use ($validated): Media {
            $station = $this->stationId ? Media::query()->where('type', MediaType::Radio)->findOrFail($this->stationId) : new Media(['type' => MediaType::Radio]);
            $station->fill(['status' => $validated['status'], 'name' => $validated['name'], 'slug' => $validated['slug'], 'description' => $validated['description'] ?: null, 'website_url' => $validated['websiteUrl'] ?: null, 'country_id' => $validated['countryId'] ?: null, 'administrative_area_id' => $validated['administrativeAreaId'] ?: null, 'city_id' => $validated['cityId'] ?: null]);
            $station->save();
            $station->radioStation()->updateOrCreate([], ['call_sign' => $validated['callSign'] ?: null, 'frequency' => $validated['frequency'] ?: null, 'frequency_unit' => $validated['frequencyUnit'] ?: null, 'source_state' => $validated['sourceState'] ?: null, 'latitude' => $validated['latitude'] ?: null, 'longitude' => $validated['longitude'] ?: null]);
            $station->categories()->sync($validated['categoryIds'] ?? []);
            $station->genres()->sync($validated['genreIds'] ?? []);
            $languages = collect($validated['languageIds'] ?? [])->mapWithKeys(fn ($id) => [$id => ['is_primary' => (string) $id === (string) $validated['primaryLanguageId']]])->all();
            $station->languages()->sync($languages);
            $station->artworks()->where('is_primary', true)->delete();
            if ($validated['artworkUrl']) {
                $station->artworks()->create(['kind' => ArtworkKind::Logo, 'url' => $validated['artworkUrl'], 'is_primary' => true]);
            }
            $retained = [];
            foreach ($validated['streams'] ?? [] as $index => $stream) {
                $url = trim($stream['url']);
                $record = isset($stream['id']) ? $station->streamSources()->find($stream['id']) : null;
                $record ??= $station->streamSources()->make();
                $record->fill(['url' => $url, 'resolved_url' => $stream['resolved_url'] ?: null, 'url_hash' => hash('sha256', $url), 'protocol' => parse_url($url, PHP_URL_SCHEME) ?: 'https', 'format' => strtolower($stream['format']), 'codec' => $stream['codec'] ?: null, 'bitrate_kbps' => $stream['bitrate_kbps'] ?: null, 'source_provider_id' => $stream['source_provider_id'] ?: null, 'status' => $stream['status'], 'verification_status' => $stream['verification_status'], 'is_primary' => (bool) ($stream['is_primary'] ?? $index === 0)]);
                $record->save();
                $retained[] = $record->id;
            }
            $station->streamSources()->whereNotIn('id', $retained)->delete();

            return $station;
        });

        session()->flash('success', $this->stationId ? 'Radio station updated.' : 'Radio station created.');
        $this->redirectRoute('admin.radio.show', $station, navigate: true);
    }

    public function render(): View
    {
        $countries = Country::query()->orderBy('name')->get(['id', 'name']);
        $areas = AdministrativeArea::query()->when($this->countryId, fn ($query) => $query->where('country_id', $this->countryId))->orderBy('name')->get(['id', 'name']);
        $cities = City::query()->when($this->countryId, fn ($query) => $query->where('country_id', $this->countryId))->when($this->administrativeAreaId, fn ($query) => $query->where('administrative_area_id', $this->administrativeAreaId))->orderBy('name')->get(['id', 'name']);
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);
        $genres = Genre::query()->orderBy('name')->get(['id', 'name']);
        $languages = Language::query()->orderBy('name')->get(['id', 'name']);
        $providers = SourceProvider::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('livewire.admin.radio.form', compact('countries', 'areas', 'cities', 'categories', 'genres', 'languages', 'providers'))->layoutData(['title' => $this->stationId ? 'Edit radio station' : 'Add radio station']);
    }

    private function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('media', 'slug')->where('type', MediaType::Radio->value)->ignore($this->stationId)], 'status' => ['required', Rule::enum(MediaStatus::class)], 'description' => ['nullable', 'string', 'max:10000'], 'websiteUrl' => ['nullable', 'url', 'max:2048'], 'countryId' => ['nullable', 'integer', 'exists:countries,id'], 'administrativeAreaId' => ['nullable', 'integer', 'exists:administrative_areas,id'], 'cityId' => ['nullable', 'integer', 'exists:cities,id'], 'callSign' => ['nullable', 'string', 'max:255'], 'frequency' => ['nullable', 'numeric', 'min:0', 'max:99999'], 'frequencyUnit' => ['nullable', 'string', 'max:8'], 'sourceState' => ['nullable', 'string', 'max:255'], 'latitude' => ['nullable', 'numeric', 'between:-90,90'], 'longitude' => ['nullable', 'numeric', 'between:-180,180'], 'artworkUrl' => ['nullable', 'url', 'max:2048'], 'categoryIds' => ['array'], 'categoryIds.*' => ['integer', 'exists:categories,id'], 'genreIds' => ['array'], 'genreIds.*' => ['integer', 'exists:genres,id'], 'languageIds' => ['array'], 'languageIds.*' => ['integer', 'exists:languages,id'], 'primaryLanguageId' => ['nullable', 'integer', 'exists:languages,id'], 'streams' => ['array'], 'streams.*.id' => ['nullable', 'integer'], 'streams.*.url' => ['required', 'url', 'max:4096'], 'streams.*.resolved_url' => ['nullable', 'url', 'max:4096'], 'streams.*.format' => ['required', 'string', 'max:32'], 'streams.*.codec' => ['nullable', 'string', 'max:32'], 'streams.*.bitrate_kbps' => ['nullable', 'integer', 'min:1', 'max:100000'], 'streams.*.source_provider_id' => ['nullable', 'integer', 'exists:source_providers,id'], 'streams.*.status' => ['required', Rule::enum(StreamStatus::class)], 'streams.*.verification_status' => ['required', Rule::enum(VerificationStatus::class)], 'streams.*.is_primary' => ['boolean']];
    }
}
