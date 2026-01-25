<?php

namespace Tests\Feature\Billing;

use App\Models\Bill;
use App\Models\Challan;
use App\Models\ChallanProduct;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DueAmountCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billingService = app(BillingService::class);
    }

    public function test_due_without_siblings(): void
    {
        $quotation = Quotation::factory()->create();
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
            'total' => 1000.00,
        ]);

        $product = Product::factory()->create();
        $qp = QuotationProduct::create([
            'product_id' => $product->id,
            'quotation_revision_id' => $revision->id,
            'unit_price' => 300.00,
            'quantity' => 1,
        ]);
        $challan = Challan::create([
            'quotation_revision_id' => $revision->id,
            'challan_no' => 'CH-DUE-001',
            'date' => now()->format('Y-m-d'),
        ]);
        $cp = ChallanProduct::create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $qp->id,
            'quantity' => 1,
        ]);

        $bill = $this->billingService->createBill([
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'bill_type' => 'regular',
            'invoice_no' => 'REG-DUE-001',
            'bill_date' => now()->format('d/m/Y'),
            'discount' => 0,
            'shipping' => 0,
            'status' => 'issued',
            'items' => [
                [
                    'quotation_product_id' => $qp->id,
                    'quantity' => 1,
                    'allocations' => [
                        [
                            'challan_product_id' => $cp->id,
                            'billed_quantity' => 1,
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals(1000.00, (float) $bill->total_amount);
        $this->assertEquals(700.00, (float) $bill->due); // 1000 - (0 + 300)
    }

    public function test_due_with_siblings_excludes_cancelled(): void
    {
        $quotation = Quotation::factory()->create();
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
            'total' => 1000.00,
        ]);

        // Existing sibling bill (issued)
        Bill::factory()->regular()->create([
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'bill_amount' => 200.00,
            'status' => 'issued',
        ]);

        // Cancelled sibling should be ignored
        Bill::factory()->regular()->create([
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'bill_amount' => 400.00,
            'status' => 'cancelled',
        ]);

        $product = Product::factory()->create();
        $qp = QuotationProduct::create([
            'product_id' => $product->id,
            'quotation_revision_id' => $revision->id,
            'unit_price' => 300.00,
            'quantity' => 1,
        ]);
        $challan = Challan::create([
            'quotation_revision_id' => $revision->id,
            'challan_no' => 'CH-DUE-002',
            'date' => now()->format('Y-m-d'),
        ]);
        $cp = ChallanProduct::create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $qp->id,
            'quantity' => 1,
        ]);

        $bill = $this->billingService->createBill([
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'bill_type' => 'regular',
            'invoice_no' => 'REG-DUE-002',
            'bill_date' => now()->format('d/m/Y'),
            'discount' => 0,
            'shipping' => 0,
            'status' => 'issued',
            'items' => [
                [
                    'quotation_product_id' => $qp->id,
                    'quantity' => 1,
                    'allocations' => [
                        [
                            'challan_product_id' => $cp->id,
                            'billed_quantity' => 1,
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals(500.00, (float) $bill->due); // 1000 - (200 + 300)
    }

    public function test_due_is_never_negative(): void
    {
        $quotation = Quotation::factory()->create();
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
            'total' => 300.00,
        ]);

        Bill::factory()->regular()->create([
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'bill_amount' => 250.00,
            'status' => 'issued',
        ]);

        $product = Product::factory()->create();
        $qp = QuotationProduct::create([
            'product_id' => $product->id,
            'quotation_revision_id' => $revision->id,
            'unit_price' => 200.00,
            'quantity' => 1,
        ]);
        $challan = Challan::create([
            'quotation_revision_id' => $revision->id,
            'challan_no' => 'CH-DUE-003',
            'date' => now()->format('Y-m-d'),
        ]);
        $cp = ChallanProduct::create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $qp->id,
            'quantity' => 1,
        ]);

        $bill = $this->billingService->createBill([
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'bill_type' => 'regular',
            'invoice_no' => 'REG-DUE-003',
            'bill_date' => now()->format('d/m/Y'),
            'discount' => 0,
            'shipping' => 0,
            'status' => 'issued',
            'items' => [
                [
                    'quotation_product_id' => $qp->id,
                    'quantity' => 1,
                    'allocations' => [
                        [
                            'challan_product_id' => $cp->id,
                            'billed_quantity' => 1,
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals(0.00, (float) $bill->due); // Clamp to 0 when negative
    }
}
