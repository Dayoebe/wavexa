<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SourceProvider extends Model
{
    protected $fillable = ['name', 'slug', 'website_url', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function mediaSources(): HasMany
    {
        return $this->hasMany(MediaSource::class);
    }

    public function streamSources(): HasMany
    {
        return $this->hasMany(StreamSource::class);
    }
}
