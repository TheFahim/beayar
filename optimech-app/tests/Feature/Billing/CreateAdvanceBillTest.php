<?php

namespace Tests\Feature\Billing;

use App\Models\Bill;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateAdvanceBillTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billingService = app(BillingService::class);
    }

    /**
     * Test creating an advance bill with snapshot values
     */
    public function test_create_advance_bill_with_snapshots(): void
    {
        $quotation = Quotation::factory()->create();
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);

        $quotationProduct = QuotationProduct::factory()->create([
            'quotation_revision_id' => $revision->id,
            'unit_price' => 150.00,
            'tax_rate' => 10,
        ]);

        $billData = [
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'bill_type' => 'advance',
            'invoice_no' => 'ADV-001',
            'bill_date' => now()->format('Y-m-d'),
            'discount' => 10.00,
            'shipping' => 5.00,
            'status' => 'draft',
            'notes' => 'Advance bill test',
            'user_id' => 1,
            'items' => [
                [
                    'quotation_product_id' => $quotationProduct->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $bill = $this->billingService->createBill($billData);

        $this->assertEquals('advance', $bill->bill_type);
        $this->assertEquals(10.00, $bill->discount);
        $this->assertEquals(5.00, $bill->shipping);
        $this->assertEquals(1, $bill->items->count());

        $billItem = $bill->items->first();
        $this->assertEquals(2, $billItem->quantity);
        $this->assertEquals(150.00, $billItem->unit_price);
        $this->assertEquals(30.00, $billItem->tax); // 10% of 150 * 2
        $this->assertEquals(330.00, $billItem->line_total); // (150 * 2) + 30

        // Verify total amount calculation: line_total - discount + shipping
        $expectedTotal = 330.00 - 10.00 + 5.00;
        $this->assertEquals($expectedTotal, $bill->total_amount);
    }

    /**
     * Test advance bill validation - requires quotation revision
     */
    public function test_advance_bill_requires_quotation_revision(): void
    {
        $quotation = Quotation::factory()->create();

        $billData = [
            'quotation_id' => $quotation->id,
            'bill_type' => 'advance',
            'invoice_no' => 'ADV-002',
            'bill_date' => now()->format('Y-m-d'),
            'user_id' => 1,
            'items' => [],
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->billingService->createBill($billData);
    }

    /**
     * Test bill item snapshots are preserved
     */
    public function test_bill_item_snapshots_are_preserved(): void
    {
        $quotation = Quotation::factory()->create();
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);

        $quotationProduct = QuotationProduct::factory()->create([
            'quotation_revision_id' => $revision->id,
            'unit_price' => 200.00,
            'tax_rate' => 15,
        ]);

        $billData = [
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'bill_type' => 'advance',
            'invoice_no' => 'ADV-003',
            'bill_date' => now()->format('Y-m-d'),
            'user_id' => 1,
            'items' => [
                [
                    'quotation_product_id' => $quotationProduct->id,
                    'quantity' => 3,
                ],
            ],
        ];

        $bill = $this->billingService->createBill($billData);
        $billItem = $bill->items->first();

        // Verify snapshot values are stored
        $this->assertEquals(3, $billItem->quantity);
        $this->assertEquals(200.00, $billItem->unit_price);
        $this->assertEquals(90.00, $billItem->tax); // 15% of 200 * 3
        $this->assertEquals(690.00, $billItem->line_total); // (200 * 3) + 90

        // Change the original quotation product price
        $quotationProduct->update(['unit_price' => 250.00]);

        // Reload bill item and verify snapshot is preserved
        $billItem->refresh();
        $this->assertEquals(200.00, $billItem->unit_price); // Should remain 200, not 250
        $this->assertEquals(690.00, $billItem->line_total); // Should remain 690
    }
}
