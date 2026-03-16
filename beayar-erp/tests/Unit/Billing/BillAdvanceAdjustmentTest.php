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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class BillAdvanceAdjustmentTest extends TestCase
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
    public function it_maintains_decimal_precision_in_calculations()
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
            'total_amount' => '33333.33',
        ]);

        BillPayment::create([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '33333.33',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $regularBill1 = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_DRAFT,
            'total_amount' => '10000.00',
            'net_payable_amount' => '10000.00',
        ]);

        $regularBill2 = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_DRAFT,
            'total_amount' => '10000.00',
            'net_payable_amount' => '10000.00',
        ]);

        $service = app(BillingService::class);

        // Apply 11111.11 to first bill
        $service->applyAdvanceCredit($advanceBill, $regularBill1, '11111.11');

        // Apply 11111.11 to second bill
        $service->applyAdvanceCredit($advanceBill, $regularBill2, '11111.11');

        // Remaining should be exactly 11111.11
        $balance = $service->getUnappliedAdvanceBalance($advanceBill);

        $this->assertEquals('11111.11', $balance);
    }

    /** @test */
    public function it_prevents_duplicate_adjustments()
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
            'payment_method' => 'cash',
            'payment_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_DRAFT,
            'total_amount' => '5000.00',
            'net_payable_amount' => '5000.00',
        ]);

        $service = app(BillingService::class);

        // First application should work
        $service->applyAdvanceCredit($advanceBill, $regularBill, '1000.00');

        // Second application to same pair should fail due to unique constraint
        $this->expectException(\Illuminate\Database\QueryException::class);

        $service->applyAdvanceCredit($advanceBill, $regularBill, '500.00');
    }

    /** @test */
    public function it_correctly_reverses_adjustment_on_cancel()
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
            'payment_method' => 'cash',
            'payment_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_DRAFT,
            'total_amount' => '15000.00',
            'advance_applied_amount' => '0.00',
            'net_payable_amount' => '15000.00',
        ]);

        $service = app(BillingService::class);
        $service->applyAdvanceCredit($advanceBill, $regularBill, '3000.00');

        $regularBill->refresh();
        $this->assertEquals('3000.00', $regularBill->advance_applied_amount);
        $this->assertEquals('12000.00', $regularBill->net_payable_amount);

        // Issue the regular bill first (required before cancellation)
        $service->issueBill($regularBill);
        $regularBill->refresh();

        // Cancel the regular bill
        $service->cancelBill($regularBill);

        // Verify reversal
        $advanceBill->refresh();
        $this->assertEquals('10000.00', $service->getUnappliedAdvanceBalance($advanceBill));
    }

    /** @test */
    public function it_tracks_multiple_adjustments_correctly()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $advanceBill1 = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_ADVANCE,
            'status' => Bill::STATUS_PAID,
            'total_amount' => '5000.00',
        ]);

        $advanceBill2 = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_ADVANCE,
            'status' => Bill::STATUS_PAID,
            'total_amount' => '7000.00',
        ]);

        BillPayment::create([
            'bill_id' => $advanceBill1->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '5000.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
        ]);

        BillPayment::create([
            'bill_id' => $advanceBill2->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '7000.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_DRAFT,
            'total_amount' => '15000.00',
            'net_payable_amount' => '15000.00',
        ]);

        $service = app(BillingService::class);

        // Apply from first advance
        $service->applyAdvanceCredit($advanceBill1, $regularBill, '3000.00');

        // Apply from second advance
        $service->applyAdvanceCredit($advanceBill2, $regularBill, '5000.00');

        $regularBill->refresh();
        $this->assertEquals('8000.00', $regularBill->advance_applied_amount);
        $this->assertEquals('7000.00', $regularBill->net_payable_amount);

        // Verify remaining balances
        $this->assertEquals('2000.00', $service->getUnappliedAdvanceBalance($advanceBill1));
        $this->assertEquals('2000.00', $service->getUnappliedAdvanceBalance($advanceBill2));
    }

    /** @test */
    public function it_handles_zero_amount_adjustment()
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

        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_DRAFT,
            'total_amount' => '5000.00',
            'net_payable_amount' => '5000.00',
        ]);

        $service = app(BillingService::class);

        // Zero amount should still work but not change values
        $service->applyAdvanceCredit($advanceBill, $regularBill, '0.00');

        $regularBill->refresh();
        $this->assertEquals('0.00', $regularBill->advance_applied_amount);
    }
}
