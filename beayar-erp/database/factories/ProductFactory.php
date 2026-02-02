<?php

namespace Database\Factories;

use App\Models\UserCompany;
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
            'user_company_id' => UserCompany::factory(),
            'name' => fake()->words(3, true),
            'unit' => fake()->randomElement(['kg', 'pcs', 'box', 'meter']),
        ];
    }
}
