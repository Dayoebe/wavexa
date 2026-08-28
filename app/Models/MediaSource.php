<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaSource extends Model
{
    protected $fillable = [
        'media_id', 'source_provider_id', 'external_identifier',
        'external_identifier_hash', 'source_url', 'imported_at',
        'last_synchronized_at', 'metadata',
    ];

    protected function casts(): array
    {
        return ['imported_at' => 'datetime', 'last_synchronized_at' => 'datetime', 'metadata' => 'array'];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function sourceProvider(): BelongsTo
    {
        return $this->belongsTo(SourceProvider::class);
    }
}
