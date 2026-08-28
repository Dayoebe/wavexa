<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Language extends Model
{
    protected $fillable = ['name', 'iso_639_1', 'iso_639_3'];

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class)->withPivot('is_primary');
    }
}
