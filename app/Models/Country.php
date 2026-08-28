<?php

namespace App\Models;

use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    protected $fillable = ['name', 'iso_alpha_2', 'iso_alpha_3', 'iso_numeric'];

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
}
