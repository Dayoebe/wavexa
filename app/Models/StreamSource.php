<?php

namespace App\Models;

use App\Enums\StreamStatus;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StreamSource extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'media_id', 'source_provider_id', 'url', 'resolved_url', 'url_hash',
        'protocol', 'format', 'codec', 'bitrate_kbps', 'status',
        'verification_status', 'is_primary', 'last_checked_at',
        'last_successful_at', 'failure_count',
        'response_time_ms', 'http_status', 'content_type', 'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => StreamStatus::class,
            'verification_status' => VerificationStatus::class,
            'is_primary' => 'boolean',
            'last_checked_at' => 'datetime',
            'last_successful_at' => 'datetime',
        ];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function sourceProvider(): BelongsTo
    {
        return $this->belongsTo(SourceProvider::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(StreamReport::class);
    }

    public function healthChecks(): HasMany
    {
        return $this->hasMany(StreamHealthCheck::class);
    }

    public function geoRules(): HasMany
    {
        return $this->hasMany(StreamGeoRule::class);
    }
}
