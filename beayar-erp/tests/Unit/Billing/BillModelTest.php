<?php

namespace Tests\Unit\Billing;

use Tests\TestCase;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\TenantCompany;
use App\Models\User;
use App\Exceptions\BillLockedException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillModelTest extends TestCase
{
    use RefreshDatabase;

    protected TenantCompany $tenant;
    protected User $user;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = TenantCompany::factory()->create();
        $this->user = User::factory()->forTenant($this->tenant->id)->create();
        $this->customer = Customer::factory()->create(['tenant_company_id' => $this->tenant->id]);

        session(['tenant_company_id' => $this->tenant->id]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_be_edited_when_draft()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'status' => Bill::STATUS_DRAFT,
            'bill_type' => Bill::TYPE_REGULAR,
        ]);

        $this->assertTrue($bill->canBeEdited());
        $this->assertNull($bill->getLockReason());
    }

    /** @test */
    public function it_cannot_be_edited_when_not_draft()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'status' => Bill::STATUS_ISSUED,
            'bill_type' => Bill::TYPE_REGULAR,
        ]);

        $this->assertFalse($bill->canBeEdited());
        $this->assertEquals(Bill::LOCK_REASON_STATUS, $bill->getLockReason());
    }

    /** @test */
    public function advance_bill_cannot_be_edited_when_child_is_issued()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'status' => Bill::STATUS_DRAFT,
            'bill_type' => Bill::TYPE_ADVANCE,
        ]);

        Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'parent_bill_id' => $advanceBill->id,
            'status' => Bill::STATUS_ISSUED,
            'bill_type' => Bill::TYPE_RUNNING,
        ]);

        $this->assertFalse($advanceBill->canBeEdited());
        $this->assertEquals(Bill::LOCK_REASON_CHILD, $advanceBill->getLockReason());
    }

    /** @test */
    public function it_cannot_be_edited_when_has_payments()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'status' => Bill::STATUS_DRAFT,
            'bill_type' => Bill::TYPE_REGULAR,
        ]);

        BillPayment::create([
            'bill_id' => $bill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '1000.00',
            'payment_method' => 'cash',
            'payment_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $bill->refresh();
        $this->assertFalse($bill->canBeEdited());
        $this->assertEquals(Bill::LOCK_REASON_PAYMENTS, $bill->getLockReason());
    }

    /** @test */
    public function advance_bill_cannot_be_edited_when_credit_applied()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'status' => Bill::STATUS_DRAFT,
            'bill_type' => Bill::TYPE_ADVANCE,
        ]);

        $dummyBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_DRAFT,
        ]);
        \DB::table('bill_advance_adjustments')->insert([
            'advance_bill_id' => $advanceBill->id,
            'final_bill_id' => $dummyBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '500.00',
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $advanceBill->refresh();
        $this->assertFalse($advanceBill->canBeEdited());
        $this->assertEquals(Bill::LOCK_REASON_ADVANCE, $advanceBill->getLockReason());
    }

    /** @test */
    public function regular_bill_cannot_be_edited_when_has_adjustments_and_not_draft()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'status' => Bill::STATUS_ISSUED, // Non-draft status
            'bill_type' => Bill::TYPE_REGULAR,
        ]);

        $dummyAdvanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_ADVANCE,
            'status' => Bill::STATUS_PAID,
        ]);
        \DB::table('bill_advance_adjustments')->insert([
            'advance_bill_id' => $dummyAdvanceBill->id,
            'final_bill_id' => $regularBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '500.00',
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $regularBill->refresh();
        $this->assertFalse($regularBill->canBeEdited());
        // Issued bills are locked by status (Rule 1), not by adjustments (Rule 6)
        $this->assertEquals(Bill::LOCK_REASON_STATUS, $regularBill->getLockReason());
    }

    /** @test */
    public function it_throws_exception_on_update_when_locked()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'status' => Bill::STATUS_ISSUED,
            'bill_type' => Bill::TYPE_REGULAR,
            'total_amount' => '1000.00',
        ]);

        $this->expectException(BillLockedException::class);

        $bill->update(['total_amount' => '2000.00']);
    }

    /** @test */
    public function it_allows_lock_field_updates_even_when_locked()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'status' => Bill::STATUS_ISSUED,
            'bill_type' => Bill::TYPE_REGULAR,
            'is_locked' => true,
        ]);

        // This should not throw
        $bill->update([
            'is_locked' => true,
            'lock_reason' => Bill::LOCK_REASON_STATUS,
        ]);

        $this->assertTrue($bill->fresh()->is_locked);
    }

    /** @test */
    public function it_calculates_remaining_balance_correctly()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'status' => Bill::STATUS_ISSUED,
            'bill_type' => Bill::TYPE_REGULAR,
            'total_amount' => '10000.00',
            'net_payable_amount' => '8000.00', // After advance
        ]);

        BillPayment::create([
            'bill_id' => $bill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '3000.00',
            'payment_method' => 'cash',
            'payment_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $bill->refresh();

        $this->assertEquals('3000.00', $bill->paid_amount);
        $this->assertEquals('5000.00', $bill->remaining_balance);
        $this->assertFalse($bill->is_fully_paid);
    }

    /** @test */
    public function it_calculates_is_fully_paid_correctly()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'status' => Bill::STATUS_ISSUED,
            'bill_type' => Bill::TYPE_REGULAR,
            'total_amount' => '10000.00',
            'net_payable_amount' => '10000.00',
        ]);

        BillPayment::create([
            'bill_id' => $bill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '10000.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $bill->refresh();

        $this->assertEquals('10000.00', $bill->paid_amount);
        $this->assertEquals('0.00', $bill->remaining_balance);
        $this->assertTrue($bill->is_fully_paid);
    }

    /** @test */
    public function scopes_filter_correctly()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'status' => Bill::STATUS_DRAFT,
            'bill_type' => Bill::TYPE_ADVANCE,
        ]);

        Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'status' => Bill::STATUS_ISSUED,
            'bill_type' => Bill::TYPE_REGULAR,
        ]);

        Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'status' => Bill::STATUS_PAID,
            'bill_type' => Bill::TYPE_RUNNING,
        ]);

        $this->assertEquals(1, Bill::forTenant()->draft()->count());
        $this->assertEquals(1, Bill::forTenant()->issued()->count());
        $this->assertEquals(1, Bill::forTenant()->advance()->count());
        $this->assertEquals(1, Bill::forTenant()->regular()->count());
        $this->assertEquals(1, Bill::forTenant()->running()->count());
        $this->assertEquals(2, Bill::forTenant()->unpaid()->count());
    }

    /** @test */
    public function it_locks_and_unlocks_correctly()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'status' => Bill::STATUS_DRAFT,
            'bill_type' => Bill::TYPE_REGULAR,
            'is_locked' => false,
        ]);

        $this->assertFalse($bill->is_locked);

        $bill->lock(Bill::LOCK_REASON_CHALLAN);

        $this->assertTrue($bill->fresh()->is_locked);
        $this->assertEquals(Bill::LOCK_REASON_CHALLAN, $bill->fresh()->lock_reason);
        $this->assertNotNull($bill->fresh()->locked_at);

        $bill->unlock();

        $this->assertFalse($bill->fresh()->is_locked);
        $this->assertNull($bill->fresh()->lock_reason);
        $this->assertNull($bill->fresh()->locked_at);
    }
}
