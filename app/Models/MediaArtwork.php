<?php

namespace App\Models;

use App\Enums\ArtworkKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaArtwork extends Model
{
    protected $fillable = ['media_id', 'kind', 'url', 'disk', 'path', 'width', 'height', 'is_primary'];

    protected function casts(): array
    {
        return ['kind' => ArtworkKind::class, 'is_primary' => 'boolean'];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
