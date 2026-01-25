<?php

namespace Tests\Feature\Billing;

use App\Models\Challan;
use App\Models\ChallanProduct;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegularBillingLockFlagDeprecationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Quotation $quotation;

    protected QuotationRevision $revision;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $this->quotation = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'regular_billing_locked' => true,
        ]);
        $this->revision = QuotationRevision::factory()->create([
            'quotation_id' => $this->quotation->id,
            'is_active' => true,
            'saved_as' => 'quotation',
        ]);
    }

    public function test_regular_bill_allowed_when_flag_true_and_no_advance_exists(): void
    {
        $this->actingAs($this->user);

        $product = Product::factory()->create();
        $qp = QuotationProduct::create([
            'quotation_revision_id' => $this->revision->id,
            'product_id' => $product->id,
            'quantity' => 6,
            'unit_price' => 100.00,
        ]);

        $challan = Challan::create([
            'quotation_revision_id' => $this->revision->id,
            'challan_no' => 'CH-FLAG-ONLY-001',
            'date' => now()->format('Y-m-d'),
        ]);

        $cp = ChallanProduct::create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $qp->id,
            'quantity' => 6,
        ]);

        $payload = [
            'bill_type' => 'regular',
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'invoice_no' => 'REG-FLAG-ONLY-001',
            'bill_date' => now()->format('d/m/Y'),
            'discount' => 0,
            'shipping' => 0,
            'items' => [
                [
                    'quotation_product_id' => $qp->id,
                    'quantity' => 6,
                    'allocations' => [
                        [
                            'challan_product_id' => $cp->id,
                            'billed_quantity' => 6,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->post(route('bills.store'), $payload);
        $response->assertSessionDoesntHaveErrors();
        $response->assertStatus(302);
    }

    public function test_advance_creation_does_not_set_regular_billing_locked(): void
    {
        $this->actingAs($this->user);

        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $quotation = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'regular_billing_locked' => false,
        ]);
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
            'saved_as' => 'quotation',
        ]);

        $service = app(BillingService::class);
        $payload = [
            'bill_type' => 'advance',
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'invoice_no' => 'ADV-FLAG-DOES-NOT-SET',
            'bill_date' => now()->format('Y-m-d'),
            'total_amount' => 1000.00,
            'bill_percentage' => 50,
            'bill_amount' => 500.00,
            'due' => 500.00,
        ];

        $service->createAdvance($payload);

        $quotation->refresh();
        $this->assertFalse((bool) ($quotation->regular_billing_locked ?? false));
    }
}
