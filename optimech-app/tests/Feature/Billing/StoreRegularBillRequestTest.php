<?php

namespace Tests\Feature\Billing;

use App\Models\Bill;
use App\Models\BillItem;
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

class StoreRegularBillRequestTest extends TestCase
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

    public function test_valid_regular_bill_payload_passes_validation(): void
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
            'challan_no' => 'CH-VAL-001',
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
            'invoice_no' => 'REG-VAL-001',
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

    public function test_missing_items_fails_validation(): void
    {
        $this->actingAs($this->user);

        $payload = [
            'bill_type' => 'regular',
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'invoice_no' => 'REG-NO-ITEMS',
            'bill_date' => now()->format('d/m/Y'),
        ];

        $response = $this->post(route('bills.store'), $payload);
        $response->assertSessionHasErrors(['items']);
    }

    public function test_allocations_required_for_each_item(): void
    {
        $this->actingAs($this->user);

        $product = Product::factory()->create();
        $qp = QuotationProduct::create([
            'quotation_revision_id' => $this->revision->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 100.00,
        ]);

        $payload = [
            'bill_type' => 'regular',
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'invoice_no' => 'REG-NO-ALLOC',
            'bill_date' => now()->format('d/m/Y'),
            'items' => [
                [
                    'quotation_product_id' => $qp->id,
                    'quantity' => 3,
                    'allocations' => [],
                ],
            ],
        ];

        $response = $this->post(route('bills.store'), $payload);
        $response->assertSessionHasErrors(['items.0.allocations']);
    }

    public function test_alignment_mismatch_fails_validation(): void
    {
        $this->actingAs($this->user);

        $productA = Product::factory()->create();
        $productB = Product::factory()->create();
        $qpA = QuotationProduct::create([
            'quotation_revision_id' => $this->revision->id,
            'product_id' => $productA->id,
            'quantity' => 5,
            'unit_price' => 50.00,
        ]);
        $qpB = QuotationProduct::create([
            'quotation_revision_id' => $this->revision->id,
            'product_id' => $productB->id,
            'quantity' => 5,
            'unit_price' => 60.00,
        ]);

        $challan = Challan::create([
            'quotation_revision_id' => $this->revision->id,
            'challan_no' => 'CH-ALI-001',
            'date' => now()->format('Y-m-d'),
        ]);

        $cpB = ChallanProduct::create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $qpB->id,
            'quantity' => 3,
        ]);

        $payload = [
            'bill_type' => 'regular',
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'invoice_no' => 'REG-ALI-001',
            'bill_date' => now()->format('d/m/Y'),
            'items' => [
                [
                    'quotation_product_id' => $qpA->id,
                    'quantity' => 3,
                    'allocations' => [
                        [
                            'challan_product_id' => $cpB->id,
                            'billed_quantity' => 3,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->post(route('bills.store'), $payload);
        $response->assertSessionHasErrors(['items.0.allocations.0.challan_product_id']);
    }

    public function test_quantity_reconciliation_fails_validation(): void
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
            'challan_no' => 'CH-REC-001',
            'date' => now()->format('Y-m-d'),
        ]);

        $cp = ChallanProduct::create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $qp->id,
            'quantity' => 5,
        ]);

        $payload = [
            'bill_type' => 'regular',
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'invoice_no' => 'REG-REC-001',
            'bill_date' => now()->format('d/m/Y'),
            'items' => [
                [
                    'quotation_product_id' => $qp->id,
                    'quantity' => 5,
                    'allocations' => [
                        [
                            'challan_product_id' => $cp->id,
                            'billed_quantity' => 3,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->post(route('bills.store'), $payload);
        $response->assertSessionHasErrors(['items.0.quantity']);
    }

    public function test_overbilling_fails_validation_excluding_cancelled_bills(): void
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
            'challan_no' => 'CH-OVR-001',
            'date' => now()->format('Y-m-d'),
        ]);

        $cp = ChallanProduct::create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $qp->id,
            'quantity' => 6,
        ]);

        $prevBill = Bill::create([
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'invoice_no' => 'REG-PRV-001',
            'bill_date' => now()->format('Y-m-d'),
            'bill_type' => 'regular',
            'status' => 'issued',
            'total_amount' => 100.00,
            'bill_percentage' => 100,
            'bill_amount' => 100.00,
            'due' => 0.00,
            'shipping' => 0.00,
            'discount' => 0.00,
            'notes' => '',
        ]);

        // Attach challan and get pivot id
        $prevBill->challans()->syncWithoutDetaching([$challan->id]);
        $pivotPrev = $prevBill->challans()->where('challan_id', $challan->id)->first();
        $billChallanPrevId = optional($pivotPrev)->pivot->id;

        $prevItem = BillItem::create([
            'bill_challan_id' => $billChallanPrevId,
            'quotation_product_id' => $qp->id,
            'challan_product_id' => $cp->id,
            'quantity' => 2,
            'remaining_quantity' => max(0, ($cp->quantity ?? 0) - 2),
            'unit_price' => 100.00,
            'bill_price' => 200.00,
        ]);

        $cancelledBill = Bill::create([
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'invoice_no' => 'REG-CAN-001',
            'bill_date' => now()->format('Y-m-d'),
            'bill_type' => 'regular',
            'status' => 'cancelled',
            'total_amount' => 100.00,
            'bill_percentage' => 100,
            'bill_amount' => 100.00,
            'due' => 0.00,
            'shipping' => 0.00,
            'discount' => 0.00,
            'notes' => '',
        ]);

        // Attach challan to cancelled bill and get pivot id
        $cancelledBill->challans()->syncWithoutDetaching([$challan->id]);
        $pivotCancel = $cancelledBill->challans()->where('challan_id', $challan->id)->first();
        $billChallanCancelId = optional($pivotCancel)->pivot->id;

        $cancelItem = BillItem::create([
            'bill_challan_id' => $billChallanCancelId,
            'quotation_product_id' => $qp->id,
            'challan_product_id' => $cp->id,
            'quantity' => 3,
            'remaining_quantity' => max(0, ($cp->quantity ?? 0) - 5),
            'unit_price' => 100.00,
            'bill_price' => 300.00,
        ]);

        $payload = [
            'bill_type' => 'regular',
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'invoice_no' => 'REG-OVR-001',
            'bill_date' => now()->format('d/m/Y'),
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
        $response->assertSessionHasErrors(['items.0.allocations.0.billed_quantity']);
    }
}
