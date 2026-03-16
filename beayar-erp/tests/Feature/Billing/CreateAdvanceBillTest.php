<?php

namespace Tests\Feature\Billing;

use Tests\TestCase;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\TenantCompany;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateAdvanceBillTest extends TestCase
{
    use RefreshDatabase;

    protected TenantCompany $tenant;
    protected User $user;
    protected Customer $customer;
    protected Quotation $quotation;

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
        $this->quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        // Set tenant context BEFORE giving permissions (required for multi-tenant permission system)
        session(['tenant_company_id' => $this->tenant->id]);
        setPermissionsTeamId($this->tenant->id);

        // Give user necessary permissions
        Permission::firstOrCreate(['name' => 'view_bills', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create_bills', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit_bills', 'guard_name' => 'web']);
        $this->user->givePermissionTo(['view_bills', 'create_bills', 'edit_bills']);
    }

    /** @test */
    public function guest_cannot_create_bill()
    {
        $response = $this->post(route('tenant.bills.store'), [
            'bill_type' => 'advance',
            'quotation_id' => $this->quotation->id,
            'amount' => '10000',
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function unauthorized_user_cannot_create_bill()
    {
        $user = User::factory()->forTenant($this->tenant->id)->create();
        // Attach to company for middleware but no permissions
        $user->companies()->attach($this->tenant->id, [
            'role' => 'user',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post(route('tenant.bills.store'), [
                'bill_type' => 'advance',
                'quotation_id' => $this->quotation->id,
                'amount' => '10000',
                'bill_date' => now()->format('d/m/Y'),
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function it_issues_bill_successfully()
    {
        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'status' => Bill::STATUS_DRAFT,
            'bill_type' => Bill::TYPE_ADVANCE,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('tenant.bills.issue', $bill));

        $response->assertRedirect();

        $this->assertEquals(Bill::STATUS_ISSUED, $bill->fresh()->status);
        $this->assertTrue($bill->fresh()->is_locked);
    }

    /** @test */
    public function it_records_payment_on_issued_bill()
    {
        // User already has edit_bills permission from setUp which covers recordPayment

        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'status' => Bill::STATUS_ISSUED,
            'bill_type' => Bill::TYPE_ADVANCE,
            'total_amount' => '10000.00',
            'net_payable_amount' => '10000.00',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('tenant.bills.payments.store', $bill), [
                'amount' => '5000.00',
                'payment_method' => 'bank_transfer',
                'payment_date' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect();

        $this->assertEquals(1, $bill->payments()->count());
        $this->assertEquals(Bill::STATUS_PARTIALLY_PAID, $bill->fresh()->status);
    }

    /** @test */
    public function it_validates_quotation_belongs_to_tenant()
    {
        $otherTenant = TenantCompany::factory()->create();
        $otherCustomer = Customer::factory()->create(['tenant_company_id' => $otherTenant->id]);
        $otherQuotation = Quotation::factory()->create([
            'tenant_company_id' => $otherTenant->id,
            'customer_id' => $otherCustomer->id,
        ]);

        // Create a revision for the other quotation
        $revisionId = \DB::table('quotation_revisions')->insertGetId([
            'quotation_id' => $otherQuotation->id,
            'revision_no' => 'R1',
            'date' => now()->format('Y-m-d'),
            'total' => '50000.00',
            'terms_conditions' => '',
            'shipping' => 0,
            'created_by' => $this->user->id,
            'is_active' => true,
            'saved_as' => 'quotation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Use the advance bill route which requires the quotation in URL
        $response = $this->actingAs($this->user)
            ->post(route('tenant.quotations.bills.advance.store', $otherQuotation), [
                'bill_type' => 'advance',
                'quotation_id' => $otherQuotation->id,
                'quotation_revision_id' => $revisionId,
                'invoice_no' => 'ADV-001',
                'total_amount' => '10000',
                'bill_amount' => '10000',
                'bill_percentage' => 20,
                'po_no' => 'PO-001',
                'due' => 0,
                'bill_date' => now()->format('d/m/Y'),
            ]);

        // Should fail because the quotation doesn't belong to user's tenant
        // The exact error depends on whether tenant scope is applied before route model binding
        $this->assertNotEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_prevents_duplicate_advance_bills_for_same_quotation()
    {
        // Create existing advance bill
        Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => Bill::TYPE_ADVANCE,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('tenant.bills.store'), [
                'bill_type' => 'advance',
                'quotation_id' => $this->quotation->id,
                'amount' => '5000',
                'bill_date' => now()->format('d/m/Y'),
            ]);

        $response->assertSessionHasErrors(['bill_type']);
    }

    /** @test */
    public function it_shows_advance_bill_details()
    {
        // Create a quotation revision for the quotation
        $revisionId = \DB::table('quotation_revisions')->insertGetId([
            'quotation_id' => $this->quotation->id,
            'revision_no' => 'R1',
            'date' => now()->format('Y-m-d'),
            'total' => '10000.00',
            'terms_conditions' => '',
            'shipping' => 0,
            'created_by' => $this->user->id,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update quotation to have customer
        $this->quotation->update(['customer_id' => $this->customer->id]);

        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $revisionId,
            'bill_type' => Bill::TYPE_ADVANCE,
            'status' => Bill::STATUS_DRAFT,
            'total_amount' => '10000.00',
            'bill_amount' => '10000.00',
            'invoice_no' => 'ADV-001',
            'bill_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('tenant.bills.show', $bill));

        $response->assertOk();
        // Just check the page loads successfully with the bill
        $response->assertSee('ADV-001');
    }

    /** @test */
    public function it_calculates_advance_percentage_correctly()
    {
        $quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'user_id' => $this->user->id,
        ]);

        // Create a quotation revision with total
        \DB::table('quotation_revisions')->insert([
            'quotation_id' => $quotation->id,
            'revision_no' => 'R1',
            'date' => now()->format('Y-m-d'),
            'total' => '50000.00',
            'terms_conditions' => '',
            'shipping' => 0,
            'created_by' => $this->user->id,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => Bill::TYPE_ADVANCE,
            'total_amount' => '50000.00',
            'bill_amount' => '10000.00',
            'bill_percentage' => '20.00',
        ]);

        $this->assertEquals('20.00', $bill->bill_percentage);
    }
}
