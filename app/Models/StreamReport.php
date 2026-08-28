<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamReport extends Model
{
    protected $fillable = ['stream_source_id', 'reason', 'details', 'ip_hash', 'user_agent', 'resolved_at'];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function streamSource(): BelongsTo
    {
        return $this->belongsTo(StreamSource::class);
    }
}
