<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MediaQualityService
{
    /** @return array{genres_removed:int, flagged:int, merged:int} */
    public function clean(bool $merge = true): array
    {
        $result = ['genres_removed' => 0, 'flagged' => 0, 'merged' => 0];

        Media::query()->with(['genres', 'artworks', 'sources'])->chunkById(200, function ($mediaItems) use (&$result): void {
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
            $result['merged'] = $this->mergeExactDuplicates();
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

    private function mergeExactDuplicates(): int
    {
        $merged = 0;
        Media::query()->with(['categories', 'genres', 'languages'])->get()->groupBy(fn (Media $media) => implode('|', [
            $media->type->value, $media->country_id ?: 'global', Str::lower(preg_replace('/[^\pL\pN]+/u', '', $media->name)),
        ]))->filter(fn ($group) => $group->count() > 1)->each(function ($group) use (&$merged): void {
            $target = $group->sortBy('id')->first();
            foreach ($group->where('id', '!=', $target->id) as $duplicate) {
                DB::transaction(function () use ($target, $duplicate): void {
                    $duplicate->sources()->update(['media_id' => $target->id]);
                    foreach ($duplicate->streamSources()->withTrashed()->get() as $stream) {
                        $target->streamSources()->withTrashed()->where('url_hash', $stream->url_hash)->exists()
                            ? $stream->forceDelete() : $stream->update(['media_id' => $target->id]);
                    }
                    $duplicate->artworks()->update(['media_id' => $target->id]);
                    $target->categories()->syncWithoutDetaching($duplicate->categories->modelKeys());
                    $target->genres()->syncWithoutDetaching($duplicate->genres->modelKeys());
                    $target->languages()->syncWithoutDetaching($duplicate->languages->mapWithKeys(fn ($language) => [$language->id => ['is_primary' => false]])->all());
                    $duplicate->radioStation()->delete();
                    $duplicate->tvChannel()->delete();
                    $duplicate->delete();
                });
                $merged++;
            }
        });

        return $merged;
    }
}
