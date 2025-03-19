<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Listing>
 */
class ListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(20, 300),
            'description' => substr($this->faker->paragraph(), 0, 250),
            'image' => $this->faker->imageUrl(),
            'category_id' => Category::inRandomOrder()->value('id') ?? 1,
            'status' => $this->faker->boolean(), 
        ];
    }
}
