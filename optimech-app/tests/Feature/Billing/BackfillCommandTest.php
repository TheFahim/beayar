<?php

namespace Tests\Feature\Billing;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Challan;
use App\Models\ChallanProduct;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackfillCommandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test backfill command is idempotent
     */
    public function test_backfill_command_is_idempotent(): void
    {
        $quotation = Quotation::factory()->create();
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);
        $qp = QuotationProduct::factory()->create([
            'quotation_revision_id' => $revision->id,
            'unit_price' => 50.00,
            'quantity' => 2,
        ]);
        $challan = Challan::factory()->create([
            'quotation_revision_id' => $revision->id,
        ]);
        $cp = ChallanProduct::factory()->create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $qp->id,
            'quantity' => 2,
        ]);
        $bill = Bill::factory()->create([
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'bill_type' => 'regular',
        ]);
        $bill->challans()->syncWithoutDetaching([$challan->id]);
        $pivot = $bill->challans()->where('challan_id', $challan->id)->first();
        $billChallanId = optional($pivot)->pivot->id;

        $billItem1 = BillItem::create([
            'bill_challan_id' => $billChallanId,
            'quotation_product_id' => $qp->id,
            'challan_product_id' => $cp->id,
            'unit_price' => 0,
            'bill_price' => 0,
            'quantity' => 0,
            'remaining_quantity' => 0,
        ]);

        $billItem2 = BillItem::create([
            'bill_challan_id' => $billChallanId,
            'quotation_product_id' => $qp->id,
            'challan_product_id' => $cp->id,
            'unit_price' => 50.00,
            'bill_price' => 100.00,
            'quantity' => 2,
            'remaining_quantity' => 0,
        ]);

        // Run command first time
        Artisan::call('app:backfill-bill-items-snapshot');
        $output1 = Artisan::output();

        // Run command second time
        Artisan::call('app:backfill-bill-items-snapshot');
        $output2 = Artisan::output();

        // Verify second run reports no items to process
        $this->assertStringContainsString('No bill items require backfill', $output2);
    }

    /**
     * Test backfill command populates snapshot values
     */
    public function test_backfill_command_populates_snapshots(): void
    {
        // Create a bill item that needs backfill
        $quotation = Quotation::factory()->create();
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);
        $quotationProduct = QuotationProduct::factory()->create([
            'quotation_revision_id' => $revision->id,
            'unit_price' => 75.00,
            'quantity' => 1,
        ]);
        $challan = Challan::factory()->create([
            'quotation_revision_id' => $revision->id,
        ]);
        $cp = ChallanProduct::factory()->create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $quotationProduct->id,
            'quantity' => 1,
        ]);
        $bill = Bill::factory()->create([
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'bill_type' => 'regular',
        ]);
        $bill->challans()->syncWithoutDetaching([$challan->id]);
        $pivot = $bill->challans()->where('challan_id', $challan->id)->first();
        $billChallanId = optional($pivot)->pivot->id;

        $billItem = BillItem::create([
            'bill_challan_id' => $billChallanId,
            'quotation_product_id' => $quotationProduct->id,
            'challan_product_id' => $cp->id,
            'unit_price' => 0,
            'bill_price' => 0,
            'quantity' => 0,
            'remaining_quantity' => 0,
        ]);

        Artisan::call('app:backfill-bill-items-snapshot');

        $billItem->refresh();

        // Verify snapshot values are populated
        $this->assertEquals(75.00, $billItem->unit_price);
        $this->assertEquals(1, $billItem->quantity); // Default fallback
        $this->assertEquals(75.00, $billItem->bill_price);
    }

    /**
     * Test backfill command infers quantity from challans
     */
    public function test_backfill_command_infers_quantity_from_challans(): void
    {
        // Create challan products
        $quotationProduct = QuotationProduct::factory()->create([
            'unit_price' => 100.00,
        ]);

        $challan = Challan::factory()->create();
        $challanProduct = ChallanProduct::factory()->create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $quotationProduct->id,
            'quantity' => 5,
        ]);

        // Create bill with challan
        $quotation = Quotation::factory()->create();
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);
        $bill = Bill::factory()->create([
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'bill_type' => 'regular',
        ]);
        $bill->challans()->syncWithoutDetaching([$challan->id]);
        $pivot = $bill->challans()->where('challan_id', $challan->id)->first();
        $billChallanId = optional($pivot)->pivot->id;

        $billItem = BillItem::create([
            'bill_challan_id' => $billChallanId,
            'quotation_product_id' => $quotationProduct->id,
            'challan_product_id' => $challanProduct->id,
            'unit_price' => 0,
            'bill_price' => 0,
            'quantity' => 0,
            'remaining_quantity' => 0,
        ]);

        // Link bill to challan
        // Already attached above via pivot

        Artisan::call('app:backfill-bill-items-snapshot');

        $billItem->refresh();

        // Verify quantity is inferred from challan
        $billItem->refresh();
        $this->assertEquals(5, $billItem->quantity);
        $this->assertEquals(100.00, $billItem->unit_price);
        $this->assertEquals(500.00, $billItem->bill_price);
    }

    /**
     * Test backfill command handles missing quotation products gracefully
     */
    public function test_backfill_command_handles_missing_quotation_products(): void
    {
        // Create a bill item with non-existent quotation product
        $quotation = Quotation::factory()->create();
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);
        $challan = Challan::factory()->create([
            'quotation_revision_id' => $revision->id,
        ]);
        $qp = QuotationProduct::factory()->create([
            'quotation_revision_id' => $revision->id,
            'unit_price' => 0,
        ]);
        $cp = ChallanProduct::factory()->create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $qp->id,
            'quantity' => 1,
        ]);
        $bill = Bill::factory()->create([
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'bill_type' => 'regular',
        ]);
        $bill->challans()->syncWithoutDetaching([$challan->id]);
        $pivot = $bill->challans()->where('challan_id', $challan->id)->first();
        $billChallanId = optional($pivot)->pivot->id;

        $billItem = BillItem::create([
            'bill_challan_id' => $billChallanId,
            'quotation_product_id' => 999999, // Non-existent ID
            'challan_product_id' => $cp->id,
            'unit_price' => 0,
            'bill_price' => 0,
            'quantity' => 0,
            'remaining_quantity' => 0,
        ]);

        // Command should not throw exception
        Artisan::call('app:backfill-bill-items-snapshot');

        // Bill item should remain unchanged
        $billItem->refresh();
        $this->assertEquals(0, $billItem->unit_price);
        $this->assertEquals(0, $billItem->bill_price);
    }
}
