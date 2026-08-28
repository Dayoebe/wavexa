<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Country> */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        $alpha2 = fake()->unique()->lexify('??');

        return [
            'name' => fake()->unique()->country(),
            'iso_alpha_2' => strtoupper($alpha2),
            'iso_alpha_3' => strtoupper($alpha2.fake()->randomLetter()),
            'iso_numeric' => fake()->unique()->numerify('###'),
        ];
    }
}
