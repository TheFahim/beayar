<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Bill::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quotation_id' => Quotation::factory(),
            'quotation_revision_id' => null,
            'parent_bill_id' => null,
            'invoice_no' => 'INV-'.strtoupper($this->faker->bothify('#######')),
            'bill_date' => $this->faker->date(),
            'payment_received_date' => $this->faker->optional()->date(),
            'bill_type' => $this->faker->randomElement(['advance', 'regular', 'running']),
            'total_amount' => $this->faker->randomFloat(2, 100, 100000),
            'bill_percentage' => $this->faker->randomFloat(2, 0, 100),
            'bill_amount' => $this->faker->randomFloat(2, 0, 100000),
            'due' => $this->faker->randomFloat(2, 0, 100000),
            'shipping' => $this->faker->randomFloat(2, 0, 1000),
            'discount' => $this->faker->randomFloat(2, 0, 1000),
            'status' => $this->faker->randomElement(['draft', 'issued', 'paid', 'cancelled']),
            'notes' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that the bill is an advance bill.
     */
    public function advance(): static
    {
        return $this->state(fn (array $attributes) => [
            'bill_type' => 'advance',
            'bill_percentage' => $this->faker->randomFloat(2, 10, 100),
        ]);
    }

    /**
     * Indicate that the bill is a regular bill.
     */
    public function regular(): static
    {
        return $this->state(fn (array $attributes) => [
            'bill_type' => 'regular',
            'bill_percentage' => 100,
        ]);
    }

    /**
     * Indicate that the bill is a running bill.
     */
    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'bill_type' => 'running',
            'bill_percentage' => $this->faker->randomFloat(2, 1, 50),
        ]);
    }

    /**
     * Indicate that the bill is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'due' => 0,
        ]);
    }

    /**
     * Indicate that the bill has a parent bill (for running bills).
     */
    public function withParent(Bill $parentBill): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_bill_id' => $parentBill->id,
            'quotation_id' => $parentBill->quotation_id,
        ]);
    }
}
