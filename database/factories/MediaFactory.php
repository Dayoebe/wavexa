<?php

namespace Database\Factories;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Media> */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'type' => MediaType::Radio,
            'status' => MediaStatus::Draft,
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('####'),
            'description' => fake()->sentence(),
            'website_url' => fake()->url(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['status' => MediaStatus::Published]);
    }

    public function podcast(): static
    {
        return $this->state(fn (): array => ['type' => MediaType::Podcast]);
    }

    public function podcastEpisode(): static
    {
        return $this->state(fn (): array => ['type' => MediaType::PodcastEpisode]);
    }
}
