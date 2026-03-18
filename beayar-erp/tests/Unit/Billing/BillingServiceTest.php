<?php

namespace Tests\Unit\Billing;

use Tests\TestCase;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\TenantCompany;
use App\Models\User;
use App\Services\BillingService;
use App\Exceptions\BillLockedException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BillingService $service;
    protected TenantCompany $tenant;
    protected User $user;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(BillingService::class);

        $this->tenant = TenantCompany::factory()->create();
        $this->user = User::factory()->forTenant($this->tenant->id)->create();
        $this->customer = Customer::factory()->create(['tenant_company_id' => $this->tenant->id]);

        // Set tenant context
        session(['tenant_company_id' => $this->tenant->id]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_issues_a_bill_and_locks_it()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_DRAFT,
        ]);

        $issuedBill = $this->service->issueBill($bill);

        $this->assertEquals(Bill::STATUS_ISSUED, $issuedBill->status);
        $this->assertTrue($issuedBill->is_locked);
        $this->assertEquals(Bill::LOCK_REASON_STATUS, $issuedBill->lock_reason);
    }

    /** @test */
    public function it_cancels_a_bill_and_reverses_advance()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_ADVANCE,
            'status' => Bill::STATUS_PAID,
            'total_amount' => '10000.00',
        ]);

        // Record payment on advance
        BillPayment::create([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '10000.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_ISSUED,
            'total_amount' => '15000.00',
            'advance_applied_amount' => '5000.00',
            'net_payable_amount' => '10000.00',
        ]);

        // Create adjustment
        DB::table('bill_advance_adjustments')->insert([
            'advance_bill_id' => $advanceBill->id,
            'final_bill_id' => $regularBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '5000.00',
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cancelledBill = $this->service->cancelBill($regularBill, 'Customer request');

        $this->assertEquals(Bill::STATUS_CANCELLED, $cancelledBill->status);
        $this->assertEquals('0.00', $cancelledBill->advance_applied_amount);

        // Verify adjustment was soft deleted
        $this->assertSoftDeleted('bill_advance_adjustments', [
            'advance_bill_id' => $advanceBill->id,
            'final_bill_id' => $regularBill->id,
        ]);
    }

    /** @test */
    public function it_records_payment_and_updates_status()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_ISSUED,
            'total_amount' => '10000.00',
            'net_payable_amount' => '10000.00',
        ]);

        $payment = $this->service->recordPayment($bill, [
            'amount' => '5000.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
        ]);

        $this->assertDatabaseHas('bill_payments', [
            'bill_id' => $bill->id,
            'amount' => '5000.00',
        ]);

        $bill->refresh();
        $this->assertEquals(Bill::STATUS_PARTIALLY_PAID, $bill->status);

        // Full payment
        $this->service->recordPayment($bill, [
            'amount' => '5000.00',
            'payment_method' => 'cash',
            'payment_date' => now(),
        ]);

        $bill->refresh();
        $this->assertEquals(Bill::STATUS_PAID, $bill->status);
    }

    /** @test */
    public function it_calculates_unapplied_advance_balance_correctly()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_ADVANCE,
            'status' => Bill::STATUS_PAID,
            'total_amount' => '20000.00',
        ]);

        // Total payments: 20000
        BillPayment::create([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '20000.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
        ]);

        // Applied: 8000
        $dummyBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_DRAFT,
        ]);
        DB::table('bill_advance_adjustments')->insert([
            'advance_bill_id' => $advanceBill->id,
            'final_bill_id' => $dummyBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '8000.00',
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $balance = $this->service->getUnappliedAdvanceBalance($advanceBill);

        $this->assertEquals('12000.00', $balance);
    }

    /** @test */
    public function it_applies_advance_credit_to_regular_bill()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        // Create advance bill with payment
        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_ADVANCE,
            'status' => Bill::STATUS_PAID,
            'total_amount' => '10000.00',
        ]);

        // Record payment on advance
        BillPayment::create([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '10000.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
        ]);

        // Create regular bill
        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_DRAFT,
            'total_amount' => '15000.00',
            'net_payable_amount' => '15000.00',
        ]);

        $adjustment = $this->service->applyAdvanceCredit($advanceBill, $regularBill, '5000.00');

        $this->assertDatabaseHas('bill_advance_adjustments', [
            'advance_bill_id' => $advanceBill->id,
            'final_bill_id' => $regularBill->id,
            'amount' => '5000.00',
        ]);

        $regularBill->refresh();
        $this->assertEquals('5000.00', $regularBill->advance_applied_amount);
        $this->assertEquals('10000.00', $regularBill->net_payable_amount);
    }

    /** @test */
    public function it_rejects_over_application_of_advance_credit()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_ADVANCE,
            'status' => Bill::STATUS_PAID,
            'total_amount' => '5000.00',
        ]);

        BillPayment::create([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '5000.00',
            'payment_method' => 'cash',
            'payment_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_DRAFT,
            'total_amount' => '10000.00',
            'net_payable_amount' => '10000.00',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot apply');

        $this->service->applyAdvanceCredit($advanceBill, $regularBill, '99999.00');
    }

    /** @test */
    public function it_rejects_credit_application_to_non_regular_bill()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_ADVANCE,
            'status' => Bill::STATUS_PAID,
            'total_amount' => '10000.00',
        ]);

        BillPayment::create([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '10000.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $runningBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_RUNNING,
            'status' => Bill::STATUS_DRAFT,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Target bill must be a regular bill');

        $this->service->applyAdvanceCredit($advanceBill, $runningBill, '1000.00');
    }

    /** @test */
    public function it_rejects_credit_application_from_different_quotation()
    {
        $quotation1 = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $quotation2 = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation1->id,
            'bill_type' => Bill::TYPE_ADVANCE,
            'status' => Bill::STATUS_PAID,
            'total_amount' => '10000.00',
        ]);

        BillPayment::create([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '10000.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation2->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_DRAFT,
            'total_amount' => '15000.00',
            'net_payable_amount' => '15000.00',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Both bills must belong to the same quotation');

        $this->service->applyAdvanceCredit($advanceBill, $regularBill, '5000.00');
    }
}
