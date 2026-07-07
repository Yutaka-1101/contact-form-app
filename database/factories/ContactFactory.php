<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->lastName,
            'last_name' => fake()->firstName,
            'gender' => fake()->numberBetween(1, 3),
            'email' => fake()->safeEmail,
            'tel' => fake()->numerify('0##########'),
            'address' => fake()->address,
            'building' => fake()->optional()->secondaryAddress,
            'detail' => fake()->realText(100),
            'category_id' => Category::factory(),
        ];
    }
}
