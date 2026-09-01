<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamGeoRule extends Model
{
    protected $fillable = ['stream_source_id', 'country_id', 'mode'];

    public function streamSource(): BelongsTo
    {
        return $this->belongsTo(StreamSource::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
