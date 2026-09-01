<?php

namespace Tests\Feature\Admin;

use App\Enums\MediaType;
use App\Livewire\Admin\Geography\Cities;
use App\Livewire\Admin\Geography\Countries;
use App\Livewire\Admin\Geography\Regions;
use App\Models\AdministrativeArea;
use App\Models\City;
use App\Models\Country;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GeographyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_country_region_and_city_hierarchy(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Countries::class)->set('name', 'Nigeria')->set('isoAlpha2', 'NG')->set('isoAlpha3', 'NGA')->set('isoNumeric', '566')->call('save')->assertHasNoErrors();
        $country = Country::query()->where('iso_alpha_2', 'NG')->firstOrFail();

        Livewire::test(Regions::class)->set('countryId', (string) $country->id)->set('name', 'Lagos State')->set('code', 'LA')->set('type', 'state')->call('save')->assertHasNoErrors();
        $region = AdministrativeArea::query()->where('name', 'Lagos State')->firstOrFail();

        Livewire::test(Cities::class)->set('countryId', (string) $country->id)->set('administrativeAreaId', (string) $region->id)->set('name', 'Lagos')->set('latitude', '6.5244')->set('longitude', '3.3792')->set('timezone', 'Africa/Lagos')->call('save')->assertHasNoErrors();
        $city = City::query()->where('name', 'Lagos')->firstOrFail();

        $this->assertTrue($city->country->is($country));
        $this->assertTrue($city->administrativeArea->is($region));
        $this->get(route('admin.geography.countries'))->assertOk()->assertSee('Nigeria')->assertSee('NGA');
        $this->get(route('admin.geography.regions'))->assertOk()->assertSee('Lagos State');
        $this->get(route('admin.geography.cities'))->assertOk()->assertSee('Africa/Lagos');
    }

    public function test_geography_in_use_cannot_be_deleted(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $country = Country::factory()->create(['iso_alpha_2' => 'NG']);
        $city = City::query()->create(['country_id' => $country->id, 'name' => 'Lagos']);
        Media::factory()->create(['type' => MediaType::Radio, 'country_id' => $country->id, 'city_id' => $city->id]);

        Livewire::test(Cities::class)->call('delete', $city->id)->assertHasErrors('delete');
        Livewire::test(Countries::class)->call('delete', $country->id)->assertHasErrors('delete');
        $this->assertDatabaseHas('cities', ['id' => $city->id]);
        $this->assertDatabaseHas('countries', ['id' => $country->id]);
    }

    public function test_non_admin_cannot_access_geography_management(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.geography.countries'))->assertForbidden();
    }
}
