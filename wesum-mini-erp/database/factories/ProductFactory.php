<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'name' => $this->faker->words(3, true),
            'unit' => $this->faker->randomElement(['pcs', 'kg', 'm', 'set']),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'quantity' => $this->faker->numberBetween(1, 20),
            'amount' => 0, // Will be calculated in the seeder
            'specs' => $this->faker->optional()->sentence,
            'remarks' => $this->faker->optional()->sentence,
        ];
    }
}
