<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PodcastEpisode extends Model
{
    protected $primaryKey = 'media_id';

    public $incrementing = false;

    protected $fillable = [
        'media_id', 'podcast_id', 'guid', 'guid_hash', 'season_number',
        'episode_number', 'duration_seconds', 'published_at', 'is_explicit',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'is_explicit' => 'boolean'];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class, 'podcast_id');
    }
}
