<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Quotation;
use App\Models\TenantCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quotation>
 */
class QuotationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Quotation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_company_id' => TenantCompany::factory(),
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'reference_no' => 'QT-' . date('Y') . '-' . strtoupper(fake()->lexify('?????')),
            'ship_to' => fake()->address(),
            'billing_stage' => Quotation::BILLING_STAGE_NONE,
        ];
    }

    /**
     * Indicate that the quotation has an advance billing stage.
     */
    public function advancePending(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_stage' => Quotation::BILLING_STAGE_ADVANCE_PENDING,
        ]);
    }

    /**
     * Indicate that the quotation has an advance issued billing stage.
     */
    public function advanceIssued(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_stage' => Quotation::BILLING_STAGE_ADVANCE_ISSUED,
        ]);
    }

    /**
     * Indicate that the quotation has a running in progress billing stage.
     */
    public function runningInProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_stage' => Quotation::BILLING_STAGE_RUNNING_IN_PROGRESS,
        ]);
    }

    /**
     * Indicate that the quotation has a regular pending billing stage.
     */
    public function regularPending(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_stage' => Quotation::BILLING_STAGE_REGULAR_PENDING,
        ]);
    }

    /**
     * Indicate that the quotation billing is completed.
     */
    public function billingCompleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_stage' => Quotation::BILLING_STAGE_COMPLETED,
        ]);
    }

    /**
     * Indicate that the quotation billing is cancelled.
     */
    public function billingCancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_stage' => Quotation::BILLING_STAGE_CANCELLED,
        ]);
    }
}
