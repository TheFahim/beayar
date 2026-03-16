<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\Quotation;
use App\Models\TenantCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
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
            'tenant_company_id' => TenantCompany::factory(),
            'quotation_id' => Quotation::factory(),
            'invoice_no' => 'INV-' . date('Y') . '-' . str_pad(fake()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'bill_date' => now(),
            'payment_received_date' => null,
            'bill_type' => Bill::TYPE_REGULAR,
            'total_amount' => fake()->randomFloat(2, 1000, 50000),
            'bill_amount' => fake()->randomFloat(2, 1000, 50000),
            'bill_percentage' => '100.00',
            'due' => '0.00',
            'shipping' => '0.00',
            'discount' => '0.00',
            'status' => Bill::STATUS_DRAFT,
            'notes' => '',
            'is_locked' => false,
            'lock_reason' => null,
            'locked_at' => null,
            'advance_applied_amount' => '0.00',
            'net_payable_amount' => fake()->randomFloat(2, 1000, 50000),
            'reissued_from_id' => null,
            'reissued_to_id' => null,
        ];
    }

    /**
     * Indicate that the bill is an advance bill.
     */
    public function advance(): static
    {
        return $this->state(fn (array $attributes) => [
            'bill_type' => Bill::TYPE_ADVANCE,
            'invoice_no' => 'ADV-' . date('Y') . '-' . str_pad(fake()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Indicate that the bill is a running bill.
     */
    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'bill_type' => Bill::TYPE_RUNNING,
            'invoice_no' => 'RUN-' . date('Y') . '-' . str_pad(fake()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Indicate that the bill is a regular bill.
     */
    public function regular(): static
    {
        return $this->state(fn (array $attributes) => [
            'bill_type' => Bill::TYPE_REGULAR,
        ]);
    }

    /**
     * Indicate that the bill is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Bill::STATUS_DRAFT,
            'is_locked' => false,
        ]);
    }

    /**
     * Indicate that the bill is issued.
     */
    public function issued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Bill::STATUS_ISSUED,
            'is_locked' => true,
            'lock_reason' => Bill::LOCK_REASON_STATUS,
            'locked_at' => now(),
        ]);
    }

    /**
     * Indicate that the bill is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Bill::STATUS_PAID,
            'is_locked' => true,
            'lock_reason' => Bill::LOCK_REASON_STATUS,
            'locked_at' => now(),
        ]);
    }

    /**
     * Indicate that the bill is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Bill::STATUS_CANCELLED,
        ]);
    }

    /**
     * Indicate that the bill is partially paid.
     */
    public function partiallyPaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Bill::STATUS_PARTIALLY_PAID,
            'is_locked' => true,
            'lock_reason' => Bill::LOCK_REASON_STATUS,
            'locked_at' => now(),
        ]);
    }

    /**
     * Set a parent bill (for running bills).
     */
    public function withParent(Bill $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_bill_id' => $parent->id,
            'quotation_id' => $parent->quotation_id,
        ]);
    }

    /**
     * Set specific amounts.
     */
    public function withAmounts(string $total, string $netPayable = null): static
    {
        return $this->state(fn (array $attributes) => [
            'total_amount' => $total,
            'net_payable_amount' => $netPayable ?? $total,
        ]);
    }
}
