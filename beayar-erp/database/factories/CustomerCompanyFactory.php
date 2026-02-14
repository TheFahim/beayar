<?php

namespace Database\Factories;

use App\Models\TenantCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerCompany>
 */
class CustomerCompanyFactory extends Factory
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
            'company_code' => fake()->unique()->bothify('CC-####'),
            'name' => fake()->company(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'bin_no' => fake()->numerify('##########'),
        ];
    }
}
