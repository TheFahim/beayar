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
            'quotation_no' => 'QUO-' . $this->faker->unique()->numerify('######'),
            'terms_conditions' => $this->faker->optional()->paragraph,
            'subtotal' => 0, // Will be updated in the seeder
            'vat' => '15',       // Default value, will be updated
            'total' => 0,      // Will be updated in the seeder
        ];
    }
}
