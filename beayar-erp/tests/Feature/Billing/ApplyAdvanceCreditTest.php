<?php

namespace Tests\Feature\Billing;

use Tests\TestCase;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\TenantCompany;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApplyAdvanceCreditTest extends TestCase
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

        // Attach user to tenant company via company_members pivot table
        $this->user->companies()->attach($this->tenant->id, [
            'role' => 'admin',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $this->customer = Customer::factory()->create(['tenant_company_id' => $this->tenant->id]);

        // Set tenant context BEFORE giving permissions (required for multi-tenant permission system)
        session(['tenant_company_id' => $this->tenant->id]);
        setPermissionsTeamId($this->tenant->id);

        Permission::firstOrCreate(['name' => 'bills.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit_bills', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'bills.apply_advance', 'guard_name' => 'web']);
        $this->user->givePermissionTo(['bills.view', 'edit_bills', 'bills.apply_advance']);
    }

    /** @test */
    public function it_applies_advance_credit_successfully()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $advanceBill = $this->createPaidAdvanceBill($quotation, '10000.00');
        $regularBill = $this->createDraftRegularBill($quotation, '15000.00');

        $response = $this->actingAs($this->user)
            ->post(route('tenant.bills.apply-advance', $regularBill), [
                'advance_bill_id' => $advanceBill->id,
                'amount' => '5000.00',
            ]);

        $response->assertRedirect();

        $regularBill->refresh();

        $this->assertEquals('5000.00', $regularBill->advance_applied_amount);
        $this->assertEquals('10000.00', $regularBill->net_payable_amount);
    }

    /** @test */
    public function it_rejects_over_application()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $advanceBill = $this->createPaidAdvanceBill($quotation, '5000.00');
        $regularBill = $this->createDraftRegularBill($quotation, '10000.00');

        $response = $this->actingAs($this->user)
            ->post(route('tenant.bills.apply-advance', $regularBill), [
                'advance_bill_id' => $advanceBill->id,
                'amount' => '99999.99', // Way more than available
            ]);

        $response->assertSessionHasErrors(['amount']);
    }

    /** @test */
    public function it_rejects_application_to_non_regular_bill()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $advanceBill = $this->createPaidAdvanceBill($quotation, '10000.00');

        $runningBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_RUNNING,
            'status' => Bill::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('tenant.bills.apply-advance', $runningBill), [
                'advance_bill_id' => $advanceBill->id,
                'amount' => '1000.00',
            ]);

        $response->assertSessionHasErrors(['advance_bill_id']);
    }

    /** @test */
    public function it_rejects_application_from_different_quotation()
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

        $advanceBill = $this->createPaidAdvanceBill($quotation1, '10000.00');
        $regularBill = $this->createDraftRegularBill($quotation2, '15000.00');

        $response = $this->actingAs($this->user)
            ->post(route('tenant.bills.apply-advance', $regularBill), [
                'advance_bill_id' => $advanceBill->id,
                'amount' => '1000.00',
            ]);

        $response->assertSessionHasErrors(['advance_bill_id']);
    }

    /** @test */
    public function it_rejects_partial_application_exceeding_balance()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $advanceBill = $this->createPaidAdvanceBill($quotation, '10000.00');
        $regularBill1 = $this->createDraftRegularBill($quotation, '10000.00');
        $regularBill2 = $this->createDraftRegularBill($quotation, '10000.00');

        // Apply 7000 to first bill
        $this->actingAs($this->user)
            ->post(route('tenant.bills.apply-advance', $regularBill1), [
                'advance_bill_id' => $advanceBill->id,
                'amount' => '7000.00',
            ]);

        // Try to apply 4000 to second bill (only 3000 available)
        $response = $this->actingAs($this->user)
            ->post(route('tenant.bills.apply-advance', $regularBill2), [
                'advance_bill_id' => $advanceBill->id,
                'amount' => '4000.00',
            ]);

        $response->assertSessionHasErrors(['amount']);
    }

    /** @test */
    public function it_removes_advance_credit_successfully()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $advanceBill = $this->createPaidAdvanceBill($quotation, '10000.00');
        $regularBill = $this->createDraftRegularBill($quotation, '15000.00');

        // Apply credit first
        $this->actingAs($this->user)
            ->post(route('tenant.bills.apply-advance', $regularBill), [
                'advance_bill_id' => $advanceBill->id,
                'amount' => '5000.00',
            ]);

        $regularBill->refresh();
        $this->assertEquals('5000.00', $regularBill->advance_applied_amount);

        // Now remove the credit
        $adjustment = $regularBill->advanceAdjustmentsReceived()->first();

        $response = $this->actingAs($this->user)
            ->delete(route('tenant.bills.remove-advance', [$regularBill, $adjustment]));

        $response->assertRedirect();

        $regularBill->refresh();
        $this->assertEquals('0.00', $regularBill->advance_applied_amount);
    }

    protected function createPaidAdvanceBill(Quotation $quotation, string $amount): Bill
    {
        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_ADVANCE,
            'status' => Bill::STATUS_PAID,
            'total_amount' => $amount,
        ]);

        BillPayment::create([
            'bill_id' => $bill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => $amount,
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
        ]);

        return $bill;
    }

    protected function createDraftRegularBill(Quotation $quotation, string $total): Bill
    {
        return Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_DRAFT,
            'total_amount' => $total,
            'net_payable_amount' => $total,
        ]);
    }
}
