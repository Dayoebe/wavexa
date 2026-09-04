<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPlaybackHistory extends Model
{
    protected $table = 'user_playback_history';

    protected $fillable = ['user_id', 'media_id', 'play_count', 'last_played_at'];

    protected function casts(): array
    {
        return ['last_played_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
