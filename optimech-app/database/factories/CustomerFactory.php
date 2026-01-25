<?php

namespace Database\Factories;

use App\Models\Company;
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
            'company_id' => Company::factory(),
            'customer_no' => 'OM-'.$this->faker->unique()->numerify('#####'),
            'customer_name' => $this->faker->name,
            'designation' => $this->faker->jobTitle,
            'department' => $this->faker->optional()->randomElement(['Sales', 'Marketing', 'Engineering', 'Finance', 'Operations']),
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->optional()->safeEmail,
        ];
    }
}
