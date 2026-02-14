<?php

namespace Database\Factories;

use App\Models\TenantCompany;
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
            'tenant_company_id' => TenantCompany::factory(),
            'name' => fake()->words(3, true),
            'unit' => fake()->randomElement(['kg', 'pcs', 'box', 'meter']),
        ];
    }
}
