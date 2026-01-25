<?php

namespace Database\Factories;

use App\Models\Quotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuotationRevision>
 */
class QuotationRevisionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 1000, 50000);
        $discountPct = $this->faker->randomFloat(2, 0, 20);
        $discountAmt = ($subtotal * $discountPct) / 100;
        $shipping = $this->faker->randomFloat(2, 0, 500);
        $vatPercentage = $this->faker->randomElement([0, 5, 10, 15]);
        $afterDiscount = $subtotal - $discountAmt;
        $vatAmount = ($afterDiscount + $shipping) * ($vatPercentage / 100);
        $total = $afterDiscount + $shipping + $vatAmount;

        return [
            'quotation_id' => Quotation::factory(),
            'date' => $this->faker->date(),
            'type' => $this->faker->randomElement(['normal', 'via']),
            'revision_no' => 'REV-'.$this->faker->unique()->numerify('######'),
            'validity' => $this->faker->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'currency' => $this->faker->randomElement(['USD', 'BDT', 'EUR', 'GBP']),
            'exchange_rate' => (string) $this->faker->randomFloat(2, 0.5, 1.5),
            'subtotal' => $subtotal,
            'discount_percentage' => $discountPct,
            'discount_amount' => $discountAmt,
            'shipping' => $shipping,
            'vat_percentage' => $vatPercentage,
            'vat_amount' => $vatAmount,
            'total' => $total,
            'terms_conditions' => $this->faker->optional(0.7)->paragraph(3),
            'saved_as' => $this->faker->randomElement(['draft', 'quotation']),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
