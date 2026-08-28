<?php

namespace App\Services\Geography;

use App\Models\Country;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class CountryResolver
{
    public function resolve(?string $code, ?string $name = null): ?Country
    {
        $code = Str::upper(trim((string) $code));

        if (strlen($code) !== 2) {
            return null;
        }

        $country = Country::query()->where('iso_alpha_2', $code)->first();

        if ($country) {
            return $country;
        }

        try {
            return Country::query()->create([
                'name' => trim((string) $name) ?: $code,
                'iso_alpha_2' => $code,
                'iso_alpha_3' => null,
                'iso_numeric' => null,
            ]);
        } catch (QueryException) {
            return Country::query()->where('iso_alpha_2', $code)->first();
        }
    }
}
