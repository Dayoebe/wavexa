<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TvChannel extends Model
{
    protected $primaryKey = 'media_id';

    public $incrementing = false;

    protected $fillable = ['media_id', 'call_sign'];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
