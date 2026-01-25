<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\QuotationRevision;
use App\Models\Specification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuotationProduct>
 */
class QuotationProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 100);
        $unitPrice = $this->faker->randomFloat(2, 10, 1000);
        $foreignCurrencyBuying = $this->faker->randomFloat(2, 5, 500);
        $bdtBuying = $foreignCurrencyBuying * $this->faker->randomFloat(2, 80, 120); // Assuming exchange rate
        $weight = $this->faker->randomFloat(2, 0.1, 50);
        $airSeaFreight = $weight * $this->faker->randomFloat(2, 2, 10);
        $tax = $unitPrice * $quantity * $this->faker->randomFloat(2, 0.05, 0.15);
        $att = $this->faker->randomFloat(2, 0, 100);
        $margin = $this->faker->randomFloat(2, 0.1, 0.3);

        return [
            'product_id' => Product::factory(),
            'quotation_revision_id' => QuotationRevision::factory(),
            'size' => $this->faker->optional(0.8)->randomElement(['Small', 'Medium', 'Large', 'XL', 'Custom']),
            'specification_id' => $this->faker->optional(0.6)->randomElement([null, Specification::factory()]),
            'unit' => $this->faker->randomFloat(2, 1, 10),
            'delivery_time' => $this->faker->numberBetween(7, 90), // Days
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'foreign_currency_buying' => $foreignCurrencyBuying,
            'bdt_buying' => $bdtBuying,
            'air_sea_freight' => $airSeaFreight,
            'weight' => $weight,
            'tax' => $tax,
            'att' => $att,
            'margin' => $margin,
        ];
    }
}
