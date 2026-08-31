<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

final class MediaDiscoveryOrder
{
    /** @var list<string> */
    private const EUROPEAN_COUNTRY_CODES = [
        'AL', 'AD', 'AT', 'BY', 'BE', 'BA', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE',
        'FI', 'FR', 'DE', 'GR', 'HU', 'IS', 'IE', 'IT', 'XK', 'LV', 'LI', 'LT',
        'LU', 'MT', 'MD', 'MC', 'ME', 'NL', 'MK', 'NO', 'PL', 'PT', 'RO', 'RU',
        'SM', 'RS', 'SK', 'SI', 'ES', 'SE', 'CH', 'UA', 'GB', 'VA',
    ];

    public static function countriesFirst(Builder $query): Builder
    {
        $europeanCodes = collect(self::EUROPEAN_COUNTRY_CODES)
            ->map(fn (string $code): string => "'{$code}'")
            ->implode(', ');

        return $query->orderByRaw(<<<SQL
            CASE
                WHEN (SELECT iso_alpha_2 FROM countries WHERE countries.id = media.country_id) = 'NG' THEN 0
                WHEN (SELECT iso_alpha_2 FROM countries WHERE countries.id = media.country_id) = 'US' THEN 1
                WHEN (SELECT iso_alpha_2 FROM countries WHERE countries.id = media.country_id) IN ({$europeanCodes}) THEN 2
                ELSE 3
            END
        SQL);
    }
}
