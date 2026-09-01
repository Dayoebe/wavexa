<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlaybackFormat extends Model
{
    protected $fillable = ['key', 'label', 'media_kind', 'mime_type', 'uses_hls', 'is_enabled'];

    protected function casts(): array
    {
        return ['uses_hls' => 'boolean', 'is_enabled' => 'boolean'];
    }

    public function streamSources(): HasMany
    {
        return $this->hasMany(StreamSource::class, 'format', 'key');
    }
}
