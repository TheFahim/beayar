<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReceivedBill>
 */
class ReceivedBillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => $this->faker->randomFloat(2, 500, 5000),
            'received_date' => $this->faker->dateTimeBetween('+1 month', '+3 months'),
            'details' => $this->faker->optional()->sentence,        ];
    }
}
