<?php

namespace App\Services\Taxonomy;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TaxonomyService
{
    public function merge(string $kind, int $sourceId, int $targetId): void
    {
        abort_if($sourceId === $targetId, 422);
        $model = $this->model($kind);
        $source = $model::query()->with('media')->findOrFail($sourceId);
        $target = $model::query()->findOrFail($targetId);

        DB::transaction(function () use ($kind, $source, $target): void {
            foreach ($source->media as $media) {
                if ($kind === 'languages') {
                    $sourcePrimary = (bool) $media->languages()->whereKey($source->id)->first()?->pivot?->is_primary;
                    $targetPrimary = (bool) $media->languages()->whereKey($target->id)->first()?->pivot?->is_primary;
                    $media->languages()->syncWithoutDetaching([$target->id => ['is_primary' => $sourcePrimary || $targetPrimary]]);
                } else {
                    $media->{$kind}()->syncWithoutDetaching([$target->id]);
                }
            }
            $source->media()->detach();
            $source->delete();
        });
    }

    public function deleteUnused(string $kind, int $id): bool
    {
        $model = $this->model($kind);
        $term = $model::query()->withCount('media')->findOrFail($id);
        if ($term->media_count > 0) {
            return false;
        }
        $term->delete();

        return true;
    }

    /** @return class-string<Model> */
    private function model(string $kind): string
    {
        return match ($kind) {
            'categories' => Category::class,
            'genres' => Genre::class,
            'languages' => Language::class,
            default => abort(404),
        };
    }
}
