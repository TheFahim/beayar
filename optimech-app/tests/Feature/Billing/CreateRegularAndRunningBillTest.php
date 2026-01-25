<?php

namespace Tests\Feature\Billing;

use App\Models\Bill;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateRegularAndRunningBillTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billingService = app(BillingService::class);
    }

    /**
     * Test creating a regular bill
     */
    public function test_create_regular_bill(): void
    {
        $quotation = Quotation::factory()->create();
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);

        $product = Product::factory()->create();
        $quotationProduct = QuotationProduct::create([
            'product_id' => $product->id,
            'quotation_revision_id' => $revision->id,
            'unit_price' => 200.00,
            'quantity' => 3,
        ]);

        $challan = \App\Models\Challan::create([
            'quotation_revision_id' => $revision->id,
            'challan_no' => 'CH-REG-001',
            'date' => now()->format('Y-m-d'),
        ]);
        $cp = \App\Models\ChallanProduct::create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $quotationProduct->id,
            'quantity' => 3,
        ]);

        $billData = [
            'quotation_id' => $quotation->id,
            'bill_type' => 'regular',
            'invoice_no' => 'REG-001',
            'bill_date' => now()->format('d/m/Y'),
            'discount' => 20.00,
            'shipping' => 15.00,
            'status' => 'draft',
            'notes' => 'Regular bill test',
            'user_id' => 1,
            'items' => [
                [
                    'quotation_product_id' => $quotationProduct->id,
                    'quantity' => 3,
                    'allocations' => [
                        [
                            'challan_product_id' => $cp->id,
                            'billed_quantity' => 3,
                        ],
                    ],
                ],
            ],
        ];

        $bill = $this->billingService->createBill($billData);

        $this->assertEquals('regular', $bill->bill_type);
        $this->assertEquals(20.00, $bill->discount);
        $this->assertEquals(15.00, $bill->shipping);
        $this->assertEquals(1, $bill->items->count());

        $billItem = $bill->items->first();
        $this->assertEquals(3, $billItem->quantity);
        $this->assertEquals(200.00, $billItem->unit_price);
        $this->assertEquals(600.00, $billItem->bill_price); // 200 * 3

        // Verify total amount calculation: line_total - discount + shipping
        $expectedTotal = 600.00 - 20.00 + 15.00;
        $this->assertEquals($expectedTotal, $bill->bill_amount);
    }

    /**
     * Test creating a running bill with parent-child relationship
     */
    public function test_create_running_bill_with_parent_child_relationship(): void
    {
        $quotation = Quotation::factory()->create();
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);

        $advanceParent = $this->billingService->createAdvance([
            'bill_type' => 'advance',
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'invoice_no' => 'ADV-001',
            'bill_date' => now()->format('Y-m-d'),
            'total_amount' => 1000.00,
            'bill_percentage' => 0,
            'bill_amount' => 0,
            'due' => 1000.00,
            'notes' => 'Advance parent',
        ]);

        $runningBill = $this->billingService->createRunning([
            'bill_type' => 'running',
            'quotation_id' => $quotation->id,
            'parent_bill_id' => $advanceParent->id,
            'invoice_no' => 'ADV-001-A',
            'bill_date' => now()->format('d/m/Y'),
            'bill_percentage' => 10,
            'bill_amount' => 100.00,
            'total_amount' => 1000.00,
            'due' => 900.00,
            'notes' => 'First running installment',
        ]);

        $this->assertEquals('running', $runningBill->bill_type);
        $this->assertEquals($advanceParent->id, $runningBill->parent->id);
        $this->assertTrue($advanceParent->children->contains($runningBill));
        $this->assertEquals($advanceParent->quotation_id, $runningBill->quotation_id);
    }

    /**
     * Test running bill validation - must have parent bill
     */
    public function test_running_bill_requires_parent_bill(): void
    {
        $quotation = Quotation::factory()->create();
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);

        $product = Product::factory()->create();
        $quotationProduct = QuotationProduct::create([
            'product_id' => $product->id,
            'quotation_revision_id' => $revision->id,
            'unit_price' => 250.00,
            'quantity' => 1,
        ]);

        $billData = [
            'quotation_id' => $quotation->id,
            'bill_type' => 'running',
            'invoice_no' => 'RUN-002',
            'bill_date' => now()->format('d/m/Y'),
            'user_id' => 1,
            'items' => [
                [
                    'quotation_product_id' => $quotationProduct->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->billingService->createBill($billData);
    }

    /**
     * Test running bill validation - parent must be regular bill
     */
    public function test_running_bill_parent_must_be_advance(): void
    {
        $quotation = Quotation::factory()->create();
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);

        $product = Product::factory()->create();
        $quotationProduct = QuotationProduct::create([
            'product_id' => $product->id,
            'quotation_revision_id' => $revision->id,
            'unit_price' => 100.00,
            'quantity' => 1,
        ]);
        $challan = \App\Models\Challan::create([
            'quotation_revision_id' => $revision->id,
            'challan_no' => 'CH-REG-PARENT',
            'date' => now()->format('Y-m-d'),
        ]);
        $cp = \App\Models\ChallanProduct::create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $quotationProduct->id,
            'quantity' => 1,
        ]);
        $regularParent = $this->billingService->createBill([
            'quotation_id' => $quotation->id,
            'bill_type' => 'regular',
            'invoice_no' => 'REG-PARENT',
            'bill_date' => now()->format('d/m/Y'),
            'discount' => 0,
            'shipping' => 0,
            'status' => 'draft',
            'notes' => 'Regular parent',
            'items' => [
                [
                    'quotation_product_id' => $quotationProduct->id,
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

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->billingService->createRunning([
            'bill_type' => 'running',
            'quotation_id' => $quotation->id,
            'parent_bill_id' => $regularParent->id,
            'invoice_no' => 'REG-PARENT-A',
            'bill_date' => now()->format('d/m/Y'),
            'bill_percentage' => 10,
            'bill_amount' => 100.00,
            'total_amount' => 1000.00,
            'due' => 900.00,
        ]);
    }
}
