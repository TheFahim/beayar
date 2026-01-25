<?php

namespace Tests\Feature\Billing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EditExistingMigrationsAreCorrectTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that bills table has the new columns and constraints
     */
    public function test_bills_table_has_new_columns(): void
    {
        $this->assertTrue(\Schema::hasColumn('bills', 'parent_bill_id'));
        $this->assertTrue(\Schema::hasColumn('bills', 'discount'));

        // Test enum constraint
        $bill = \App\Models\Bill::factory()->create([
            'bill_type' => 'advance',
        ]);
        $this->assertEquals('advance', $bill->bill_type);

        $bill = \App\Models\Bill::factory()->create([
            'bill_type' => 'regular',
        ]);
        $this->assertEquals('regular', $bill->bill_type);

        $bill = \App\Models\Bill::factory()->create([
            'bill_type' => 'running',
        ]);
        $this->assertEquals('running', $bill->bill_type);
    }

    /**
     * Test that bill_items table has snapshot fields
     */
    public function test_bill_items_table_has_snapshot_fields(): void
    {
        $this->assertTrue(\Schema::hasColumn('bill_items', 'quantity'));
        $this->assertTrue(\Schema::hasColumn('bill_items', 'unit_price'));
        $this->assertTrue(\Schema::hasColumn('bill_items', 'bill_price'));
        $this->assertTrue(\Schema::hasColumn('bill_items', 'remaining_quantity'));

        $quotation = \App\Models\Quotation::factory()->create();
        $revision = \App\Models\QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);
        $qp = \App\Models\QuotationProduct::factory()->create([
            'quotation_revision_id' => $revision->id,
            'unit_price' => 100.50,
            'quantity' => 5,
        ]);
        $challan = \App\Models\Challan::create([
            'quotation_revision_id' => $revision->id,
            'challan_no' => 'CH-TST-001',
            'date' => now()->format('Y-m-d'),
        ]);
        $cp = \App\Models\ChallanProduct::create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $qp->id,
            'quantity' => 5,
        ]);
        $bill = \App\Models\Bill::factory()->create([
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'bill_type' => 'regular',
            'invoice_no' => 'INV-TST-001',
        ]);
        $bill->challans()->syncWithoutDetaching([$challan->id]);
        $pivot = $bill->challans()->where('challan_id', $challan->id)->first();
        $billChallanId = optional($pivot)->pivot->id;

        $billItem = \App\Models\BillItem::create([
            'bill_challan_id' => $billChallanId,
            'quotation_product_id' => $qp->id,
            'challan_product_id' => $cp->id,
            'quantity' => 5,
            'unit_price' => 100.50,
            'bill_price' => 502.50,
            'remaining_quantity' => 0,
        ]);

        $this->assertEquals(5, $billItem->quantity);
        $this->assertEquals(100.50, $billItem->unit_price);
        $this->assertEquals(502.50, $billItem->bill_price);
        $this->assertEquals(0, $billItem->remaining_quantity);
    }

    /**
     * Test that bill_items foreign keys enforce constraints
     */
    public function test_bill_items_foreign_key_constraints(): void
    {
        $this->assertTrue(\Schema::hasColumn('bill_items', 'bill_challan_id'));
        $this->assertTrue(\Schema::hasColumn('bill_items', 'challan_product_id'));

        $quotation = \App\Models\Quotation::factory()->create();
        $revision = \App\Models\QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);
        $qp = \App\Models\QuotationProduct::factory()->create([
            'quotation_revision_id' => $revision->id,
            'unit_price' => 100.00,
            'quantity' => 1,
        ]);
        $challan = \App\Models\Challan::create([
            'quotation_revision_id' => $revision->id,
            'challan_no' => 'CH-FK-001',
            'date' => now()->format('Y-m-d'),
        ]);
        $cp = \App\Models\ChallanProduct::create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $qp->id,
            'quantity' => 1,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        \App\Models\BillItem::create([
            'bill_challan_id' => 999999, // invalid
            'quotation_product_id' => $qp->id,
            'challan_product_id' => $cp->id,
            'quantity' => 1,
            'remaining_quantity' => 0,
            'unit_price' => 1.00,
            'bill_price' => 1.00,
        ]);
    }

    /**
     * Test parent-child bill relationships
     */
    public function test_parent_child_bill_relationships(): void
    {
        $parentBill = \App\Models\Bill::factory()->create([
            'bill_type' => 'regular',
        ]);

        $childBill = \App\Models\Bill::factory()->create([
            'bill_type' => 'running',
            'parent_bill_id' => $parentBill->id,
            'quotation_id' => $parentBill->quotation_id,
        ]);

        $this->assertEquals($parentBill->id, $childBill->parent->id);
        $this->assertTrue($parentBill->children->contains($childBill));
    }
}
