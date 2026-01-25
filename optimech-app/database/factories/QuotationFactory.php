<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quotation>
 */
class QuotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => \App\Models\Customer::factory(),
            'quotation_no' => 'QUO-'.$this->faker->unique()->numerify('######'),
            'po_no' => $this->faker->optional()->numerify('PO-#######'),
            'po_date' => $this->faker->optional()->date(),
            'ship_to' => json_encode([
                'name' => $this->faker->company,
                'address' => $this->faker->address,
                'city' => $this->faker->city,
                'country' => $this->faker->country,
                'postal_code' => $this->faker->postcode,
            ]),
            'status' => $this->faker->randomElement(['in_progress', 'completed', 'cancelled', 'pending']),
        ];
    }
}
