<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Challan>
 */
class ChallanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'challan_no' => 'CHLN-' . $this->faker->unique()->numerify('######'),
            'po_no' => 'PO-' . $this->faker->unique()->numerify('######'),
            'delivery_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),        ];
    }
}
