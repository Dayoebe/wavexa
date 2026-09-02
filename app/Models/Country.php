<?php

namespace App\Models;

use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    protected $fillable = ['name', 'iso_alpha_2', 'iso_alpha_3', 'iso_numeric'];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'min_latitude' => 'float',
            'min_longitude' => 'float',
            'max_latitude' => 'float',
            'max_longitude' => 'float',
            'wof_synced_at' => 'datetime',
        ];
    }

    public function administrativeAreas(): HasMany
    {
        return $this->hasMany(AdministrativeArea::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function promotion(): HasOne
    {
        return $this->hasOne(CountryPromotion::class);
    }
}
