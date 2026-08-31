<?php

namespace Tests\Feature;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Models\Country;
use App\Models\Media;
use App\Support\MediaDiscoveryOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaDiscoveryOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_is_prioritized_by_nigeria_then_us_then_europe(): void
    {
        foreach ([
            ['South Africa', 'ZA', 'ZAF', 'South African Signal'],
            ['France', 'FR', 'FRA', 'French Signal'],
            ['United States', 'US', 'USA', 'American Signal'],
            ['Nigeria', 'NG', 'NGA', 'Nigerian Signal'],
        ] as [$countryName, $alpha2, $alpha3, $mediaName]) {
            $country = Country::query()->create([
                'name' => $countryName,
                'iso_alpha_2' => $alpha2,
                'iso_alpha_3' => $alpha3,
            ]);

            Media::query()->create([
                'type' => MediaType::Television,
                'status' => MediaStatus::Published,
                'name' => $mediaName,
                'slug' => str($mediaName)->slug(),
                'country_id' => $country->id,
            ]);
        }

        $query = MediaDiscoveryOrder::countriesFirst(Media::query())->orderBy('name');

        $this->assertSame([
            'Nigerian Signal',
            'American Signal',
            'French Signal',
            'South African Signal',
        ], $query->pluck('name')->all());
    }
}
