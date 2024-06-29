<?php

namespace Database\Factories;

use App\Models\Feed;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TikTokSource>
 */
class TikTokSourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique->name,
            'fan_count' => $this->faker->numberBetween(20000, 2000000),
            'feed_id' => $this->faker->numberBetween(1, Feed::count())
        ];
    }
}
