<?php

namespace App\Services\Media;

use App\Enums\MediaType;
use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MediaQualityService
{
    public function duplicateSignature(Media $media): string
    {
        return implode('|', [
            $media->type->value,
            $media->country_id ?: 'global',
            Str::lower(preg_replace('/[^\pL\pN]+/u', '', $media->name)),
        ]);
    }

    /** @param list<int> $stationIds */
    public function mergeRadioGroup(array $stationIds, int $targetId): int
    {
        return $this->mergeGroup($stationIds, $targetId, MediaType::Radio);
    }

    /** @param list<int> $channelIds */
    public function mergeTelevisionGroup(array $channelIds, int $targetId): int
    {
        return $this->mergeGroup($channelIds, $targetId, MediaType::Television);
    }

    /** @param list<int> $mediaIds */
    private function mergeGroup(array $mediaIds, int $targetId, MediaType $type): int
    {
        $items = Media::query()->where('type', $type)
            ->whereIn('id', $mediaIds)->with(['categories', 'genres', 'languages', 'radioStation', 'tvChannel'])->get();
        $target = $items->firstWhere('id', $targetId);
        abort_unless($target && $items->count() === count(array_unique($mediaIds)), 422);
        abort_unless($items->map(fn (Media $item) => $this->duplicateSignature($item))->unique()->count() === 1, 422);

        foreach ($items->where('id', '!=', $targetId) as $duplicate) {
            $this->mergeInto($target, $duplicate);
        }

        return max(0, $items->count() - 1);
    }

    /** @return array{genres_removed:int, flagged:int, merged:int} */
    public function clean(bool $merge = true, ?MediaType $type = null): array
    {
        $result = ['genres_removed' => 0, 'flagged' => 0, 'merged' => 0];

        Media::query()->when($type, fn ($query) => $query->where('type', $type))->with(['genres', 'artworks', 'sources'])->chunkById(200, function ($mediaItems) use (&$result): void {
            foreach ($mediaItems as $media) {
                $noisy = $media->genres->filter(fn ($genre) => $this->isNoisyTag($genre->name));
                if ($noisy->isNotEmpty()) {
                    $media->genres()->detach($noisy->modelKeys());
                    $result['genres_removed'] += $noisy->count();
                }
                $flags = collect([
                    $media->artworks->isEmpty() ? 'missing_artwork' : null,
                    mb_strlen($media->name) > 180 ? 'long_name' : null,
                    $media->country_id === null ? 'missing_country' : null,
                    $media->primaryStream()->exists() ? null : 'missing_stream',
                ])->filter()->values()->all();
                foreach ($media->sources as $source) {
                    $metadata = $source->metadata ?? [];
                    $metadata['quality_flags'] = $flags;
                    $source->update(['metadata' => $metadata]);
                }
                if ($flags !== []) {
                    $result['flagged']++;
                }
            }
        });

        if ($merge) {
            $result['merged'] = $this->mergeExactDuplicates($type);
        }

        return $result;
    }

    private function isNoisyTag(string $name): bool
    {
        $value = Str::lower(trim($name));

        return mb_strlen($value) > 40
            || preg_match('/^#?\d+(\.\d+)?\s*(fm|am|mhz|khz)?$/i', $value)
            || str_contains($value, 'http')
            || str_word_count($value) > 6;
    }

    private function mergeExactDuplicates(?MediaType $type = null): int
    {
        $merged = 0;
        Media::query()->when($type, fn ($query) => $query->where('type', $type))->with(['categories', 'genres', 'languages'])->get()->groupBy(fn (Media $media) => $this->duplicateSignature($media))->filter(fn ($group) => $group->count() > 1)->each(function ($group) use (&$merged): void {
            $target = $group->sortBy('id')->first();
            foreach ($group->where('id', '!=', $target->id) as $duplicate) {
                $this->mergeInto($target, $duplicate);
                $merged++;
            }
        });

        return $merged;
    }

    private function mergeInto(Media $target, Media $duplicate): void
    {
        DB::transaction(function () use ($target, $duplicate): void {
            $target->fill([
                'description' => $target->description ?: $duplicate->description,
                'website_url' => $target->website_url ?: $duplicate->website_url,
                'administrative_area_id' => $target->administrative_area_id ?: $duplicate->administrative_area_id,
                'city_id' => $target->city_id ?: $duplicate->city_id,
            ])->save();
            $duplicate->sources()->update(['media_id' => $target->id]);
            foreach ($duplicate->streamSources()->withTrashed()->get() as $stream) {
                $target->streamSources()->withTrashed()->where('url_hash', $stream->url_hash)->exists()
                    ? $stream->forceDelete() : $stream->update(['media_id' => $target->id, 'is_primary' => false]);
            }
            if ($target->artworks()->where('is_primary', true)->exists()) {
                $duplicate->artworks()->update(['media_id' => $target->id, 'is_primary' => false]);
            } else {
                $duplicate->artworks()->update(['media_id' => $target->id]);
            }
            $target->categories()->syncWithoutDetaching($duplicate->categories->modelKeys());
            $target->genres()->syncWithoutDetaching($duplicate->genres->modelKeys());
            $target->languages()->syncWithoutDetaching($duplicate->languages->mapWithKeys(fn ($language) => [$language->id => ['is_primary' => false]])->all());
            $duplicate->radioStation()->delete();
            $duplicate->tvChannel()->delete();
            $duplicate->delete();
        });
    }
}
