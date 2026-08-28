<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        collect([
            ['name' => 'Nigeria', 'iso_alpha_2' => 'NG', 'iso_alpha_3' => 'NGA', 'iso_numeric' => '566'],
            ['name' => 'South Africa', 'iso_alpha_2' => 'ZA', 'iso_alpha_3' => 'ZAF', 'iso_numeric' => '710'],
            ['name' => 'United Kingdom', 'iso_alpha_2' => 'GB', 'iso_alpha_3' => 'GBR', 'iso_numeric' => '826'],
            ['name' => 'United States', 'iso_alpha_2' => 'US', 'iso_alpha_3' => 'USA', 'iso_numeric' => '840'],
        ])->each(fn (array $country) => Country::query()->updateOrCreate(
            ['iso_alpha_3' => $country['iso_alpha_3']],
            $country,
        ));

        collect([
            ['name' => 'News', 'slug' => 'news'],
            ['name' => 'Music', 'slug' => 'music'],
            ['name' => 'Sports', 'slug' => 'sports'],
            ['name' => 'Talk', 'slug' => 'talk'],
        ])->each(fn (array $category) => Category::query()->updateOrCreate(
            ['slug' => $category['slug']],
            $category,
        ));

        collect([
            ['name' => 'Afrobeats', 'slug' => 'afrobeats'],
            ['name' => 'Gospel', 'slug' => 'gospel'],
            ['name' => 'Jazz', 'slug' => 'jazz'],
            ['name' => 'Pop', 'slug' => 'pop'],
        ])->each(fn (array $genre) => Genre::query()->updateOrCreate(
            ['slug' => $genre['slug']],
            $genre,
        ));

        collect([
            ['name' => 'English', 'iso_639_1' => 'en', 'iso_639_3' => 'eng'],
            ['name' => 'French', 'iso_639_1' => 'fr', 'iso_639_3' => 'fra'],
            ['name' => 'Spanish', 'iso_639_1' => 'es', 'iso_639_3' => 'spa'],
            ['name' => 'Yoruba', 'iso_639_1' => 'yo', 'iso_639_3' => 'yor'],
        ])->each(fn (array $language) => Language::query()->updateOrCreate(
            ['iso_639_3' => $language['iso_639_3']],
            $language,
        ));
    }
}
