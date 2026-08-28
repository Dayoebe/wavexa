<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Podcast extends Model
{
    protected $primaryKey = 'media_id';

    public $incrementing = false;

    protected $fillable = ['media_id', 'feed_url', 'feed_url_hash', 'author', 'last_fetched_at'];

    protected function casts(): array
    {
        return ['last_fetched_at' => 'datetime'];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(PodcastEpisode::class, 'podcast_id');
    }
}
