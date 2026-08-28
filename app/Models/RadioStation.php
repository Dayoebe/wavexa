<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadioStation extends Model
{
    protected $primaryKey = 'media_id';

    public $incrementing = false;

    protected $fillable = [
        'media_id', 'call_sign', 'frequency', 'frequency_unit', 'source_state',
        'latitude', 'longitude', 'source_vote_count', 'source_click_count',
        'source_click_trend', 'source_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'source_changed_at' => 'datetime',
        ];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
