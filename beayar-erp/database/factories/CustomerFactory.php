<?php

namespace Database\Factories;

use App\Models\CustomerCompany;
use App\Models\UserCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
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
            'customer_company_id' => CustomerCompany::factory(),
            'customer_no' => fake()->unique()->bothify('C-####'),
            'name' => fake()->name(),
            'attention' => fake()->name(),
            'designation' => fake()->jobTitle(),
            'department' => fake()->word(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'type' => fake()->randomElement(['individual', 'company']),
        ];
    }
}
