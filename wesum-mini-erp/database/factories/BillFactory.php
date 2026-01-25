<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
class BillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bill_no' => 'BILL-' . $this->faker->unique()->numerify('######'),
            'payable' => 0, // Will be set by the seeder
            'paid' => 0,    // Will be set by the seeder
            'due' => 0,
        ];    
    }
}
