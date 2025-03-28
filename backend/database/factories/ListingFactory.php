<?php

namespace Database\Factories;

use App\Models\User;
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
            'images' => json_encode([
                'https://via.placeholder.com/640x480.png/003399?text=image1',
                'https://via.placeholder.com/640x480.png/003399?text=image2'
            ]),
            'category_id' => Category::inRandomOrder()->value('id') ?? 1,
            'user_id' => User::inRandomOrder()->value('id') ?? 1,
            'status' => $this->faker->boolean(), 
        ];
    }
}
