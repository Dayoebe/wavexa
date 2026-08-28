<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class);
    }
}
