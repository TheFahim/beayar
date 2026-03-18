<?php

namespace Database\Factories;

use App\Models\Challan;
use App\Models\Quotation;
use App\Models\TenantCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Challan>
 */
class ChallanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Challan::class;

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
            'quotation_revision_id' => null,
            'challan_no' => 'CH-' . date('Y') . '-' . str_pad(fake()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'date' => now(),
            'delivery_date' => now()->addDays(fake()->numberBetween(1, 7)),
            'status' => 'pending',
            'notes' => null,
        ];
    }

    /**
     * Set the quotation for the challan.
     */
    public function forQuotation(Quotation $quotation): static
    {
        return $this->state(fn (array $attributes) => [
            'quotation_id' => $quotation->id,
            'tenant_company_id' => $quotation->tenant_company_id,
        ]);
    }
}
