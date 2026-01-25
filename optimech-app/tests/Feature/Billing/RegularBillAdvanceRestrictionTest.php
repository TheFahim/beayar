<?php

namespace Tests\Feature\Billing;

use App\Models\Bill;
use App\Models\Challan;
use App\Models\ChallanProduct;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegularBillAdvanceRestrictionTest extends TestCase
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
        $this->quotation = Quotation::factory()->create(['customer_id' => $customer->id]);
        $this->revision = QuotationRevision::factory()->create([
            'quotation_id' => $this->quotation->id,
            'is_active' => true,
            'saved_as' => 'quotation',
        ]);
    }

    public function test_regular_bill_creation_blocked_when_advance_exists(): void
    {
        $this->actingAs($this->user);

        $product = Product::factory()->create();
        $qp = QuotationProduct::create([
            'quotation_revision_id' => $this->revision->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 100.00,
        ]);

        $challan = Challan::create([
            'quotation_revision_id' => $this->revision->id,
            'challan_no' => 'CH-ADV-LOCK-001',
            'date' => now()->format('Y-m-d'),
        ]);

        $cp = ChallanProduct::create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $qp->id,
            'quantity' => 5,
        ]);

        Bill::create([
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'invoice_no' => 'ADV-LOCK-001',
            'bill_date' => now()->format('Y-m-d'),
            'bill_type' => 'advance',
            'status' => 'issued',
            'total_amount' => 1000.00,
            'bill_percentage' => 50,
            'bill_amount' => 500.00,
            'due' => 500.00,
            'shipping' => 0.00,
            'discount' => 0.00,
            'notes' => '',
        ]);

        $payload = [
            'bill_type' => 'regular',
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'invoice_no' => 'REG-ADV-LOCK-001',
            'bill_date' => now()->format('d/m/Y'),
            'discount' => 0,
            'shipping' => 0,
            'items' => [
                [
                    'quotation_product_id' => $qp->id,
                    'quantity' => 5,
                    'allocations' => [
                        [
                            'challan_product_id' => $cp->id,
                            'billed_quantity' => 5,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->post(route('bills.store'), $payload);
        $response->assertSessionHasErrors(['bill_type']);
    }

    public function test_regular_bill_creation_succeeds_when_no_advance_exists(): void
    {
        $this->actingAs($this->user);

        $product = Product::factory()->create();
        $qp = QuotationProduct::create([
            'quotation_revision_id' => $this->revision->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 100.00,
        ]);

        $challan = Challan::create([
            'quotation_revision_id' => $this->revision->id,
            'challan_no' => 'CH-ADV-LOCK-002',
            'date' => now()->format('Y-m-d'),
        ]);

        $cp = ChallanProduct::create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $qp->id,
            'quantity' => 4,
        ]);

        $payload = [
            'bill_type' => 'regular',
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'invoice_no' => 'REG-ADV-LOCK-002',
            'bill_date' => now()->format('d/m/Y'),
            'discount' => 0,
            'shipping' => 0,
            'items' => [
                [
                    'quotation_product_id' => $qp->id,
                    'quantity' => 4,
                    'allocations' => [
                        [
                            'challan_product_id' => $cp->id,
                            'billed_quantity' => 4,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->post(route('bills.store'), $payload);
        $response->assertSessionDoesntHaveErrors();
        $response->assertStatus(302);
    }
}
