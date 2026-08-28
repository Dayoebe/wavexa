<?php

namespace Tests\Feature;

use App\Enums\MediaType;
use App\Models\Country;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_media_sections_use_independent_paginators(): void
    {
        $country = Country::factory()->create(['iso_alpha_2' => 'NG']);

        Media::factory()->count(13)->published()->create(['country_id' => $country->id, 'type' => MediaType::Radio]);
        Media::factory()->count(13)->published()->create(['country_id' => $country->id, 'type' => MediaType::Television]);

        $this->get(route('countries.show', $country->iso_alpha_2))
            ->assertOk()
            ->assertSee('Showing')
            ->assertSee('radio_page=2', false)
            ->assertSee('tv_page=2', false);

        $this->get(route('countries.show', [$country->iso_alpha_2, 'radio_page' => 2, 'tv_page' => 2]))
            ->assertOk()
            ->assertSee('13–13');
    }
}
