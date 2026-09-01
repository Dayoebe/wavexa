<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamHealthCheck extends Model
{
    protected $fillable = ['stream_source_id', 'was_healthy', 'response_time_ms', 'http_status', 'content_type', 'failure_reason', 'checked_at'];

    protected function casts(): array
    {
        return ['was_healthy' => 'boolean', 'checked_at' => 'datetime'];
    }

    public function streamSource(): BelongsTo
    {
        return $this->belongsTo(StreamSource::class);
    }
}
