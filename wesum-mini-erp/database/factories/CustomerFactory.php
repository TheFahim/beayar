<?php

namespace Database\Factories;

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
            'customer_no' => 'WC-' . $this->faker->unique()->numerify('#####'),
            'customer_name' => $this->faker->name,
            'designation' => $this->faker->jobTitle,
            'company_name' => $this->faker->company,
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'bin_no' => $this->faker->optional()->ean13,        ];
    }
}
