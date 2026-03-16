<?php

namespace Tests\Feature\Billing;

use Tests\TestCase;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\TenantCompany;
use App\Models\User;
use App\Services\BillingService;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillCorrectionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected TenantCompany $tenant;
    protected User $user;
    protected Customer $customer;
    protected BillingService $service;

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
        $this->service = app(BillingService::class);

        // Set tenant context BEFORE giving permissions (required for multi-tenant permission system)
        session(['tenant_company_id' => $this->tenant->id]);
        setPermissionsTeamId($this->tenant->id);

        Permission::firstOrCreate(['name' => 'view_bills', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'cancel_bills', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'reissue_bills', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'issue_bills', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit_bills', 'guard_name' => 'web']);
        $this->user->givePermissionTo([
            'view_bills', 'cancel_bills', 'reissue_bills', 'issue_bills', 'edit_bills'
        ]);
    }

    /** @test */
    public function it_cancels_a_bill_and_reverses_advance()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $advanceBill = $this->createPaidAdvanceBill($quotation, '10000.00');
        $regularBill = $this->createIssuedRegularBill($quotation, '15000.00');

        // Apply advance credit (need to act as user for created_by)
        $this->actingAs($this->user);
        $this->service->applyAdvanceCredit($advanceBill, $regularBill, '5000.00');

        $regularBill->refresh();
        $this->assertEquals('5000.00', $regularBill->advance_applied_amount);

        // Cancel the bill
        $response = $this->actingAs($this->user)
            ->post(route('tenant.bills.cancel', $regularBill), [
                'reason' => 'Customer request',
            ]);

        $response->assertRedirect();

        $regularBill->refresh();
        $this->assertEquals(Bill::STATUS_CANCELLED, $regularBill->status);
        $this->assertEquals('0.00', $regularBill->advance_applied_amount);

        // Verify advance balance is restored
        $advanceBill->refresh();
        $this->assertEquals('10000.00', $this->service->getUnappliedAdvanceBalance($advanceBill));
    }

    /** @test */
    public function it_reissues_a_cancelled_bill()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $cancelledBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_CANCELLED,
            'total_amount' => '15000.00',
            'bill_amount' => '15000.00',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('tenant.bills.reissue', $cancelledBill), [
                'invoice_no' => 'INV-2024-0001',
                'bill_date' => now()->format('d/m/Y'),
            ]);

        $response->assertRedirect();

        // Verify new bill was created
        $newBill = Bill::where('reissued_from_id', $cancelledBill->id)->first();
        $this->assertNotNull($newBill);
        $this->assertEquals(Bill::STATUS_DRAFT, $newBill->status);
        $this->assertEquals('15000.00', $newBill->total_amount);

        // Verify old bill tracks the reissue
        $cancelledBill->refresh();
        $this->assertEquals($newBill->id, $cancelledBill->reissued_to_id);
    }

    /** @test */
    public function it_prevents_reissue_of_non_cancelled_bill()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $issuedBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_ISSUED,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('tenant.bills.reissue', $issuedBill), [
                'invoice_no' => 'INV-2024-0001',
                'bill_date' => now()->format('d/m/Y'),
            ]);

        // Should be forbidden because only cancelled bills can be reissued
        $response->assertForbidden();
    }

    /** @test */
    public function it_shows_bill_history_with_reissue_chain()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $originalBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_CANCELLED,
            'invoice_no' => 'INV-2024-0001',
        ]);

        $reissuedBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_DRAFT,
            'invoice_no' => 'INV-2024-0002',
            'reissued_from_id' => $originalBill->id,
        ]);

        // Update original to track reissue
        $originalBill->update(['reissued_to_id' => $reissuedBill->id]);

        $response = $this->actingAs($this->user)
            ->get(route('tenant.bills.show', $reissuedBill));

        $response->assertOk();
        $response->assertSee('INV-2024-0002'); // Current invoice number shown
    }

    /** @test */
    public function cancellation_requires_permission()
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
        ]);

        $userWithoutPermission = User::factory()->forTenant($this->tenant->id)->create();
        $userWithoutPermission->companies()->attach($this->tenant->id, [
            'role' => 'user',
            'is_active' => true,
            'joined_at' => now(),
        ]);
        $userWithoutPermission->givePermissionTo(['view_bills']); // Only view, not cancel

        $response = $this->actingAs($userWithoutPermission)
            ->post(route('tenant.bills.cancel', $bill), [
                'reason' => 'Test',
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function reissue_requires_permission()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        $cancelledBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_CANCELLED,
        ]);

        $userWithoutPermission = User::factory()->forTenant($this->tenant->id)->create();
        $userWithoutPermission->companies()->attach($this->tenant->id, [
            'role' => 'user',
            'is_active' => true,
            'joined_at' => now(),
        ]);
        $userWithoutPermission->givePermissionTo(['view_bills']); // Only view, not reissue

        $response = $this->actingAs($userWithoutPermission)
            ->post(route('tenant.bills.reissue', $cancelledBill), [
                'invoice_no' => 'INV-2024-0001',
                'bill_date' => now()->format('d/m/Y'),
            ]);

        $response->assertForbidden();
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

    protected function createIssuedRegularBill(Quotation $quotation, string $total): Bill
    {
        return Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_REGULAR,
            'status' => Bill::STATUS_ISSUED,
            'total_amount' => $total,
            'net_payable_amount' => $total,
        ]);
    }
}
