<?php

namespace Tests\Feature\Tenant;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Quotation;
use App\Models\QuotationRevision;
use App\Models\QuotationStatus;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantCompany;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class QuotationRevisionActivationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected $company;

    protected $customer;

    protected $quotation;

    protected $revision1;

    protected $revision2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Create user and tenant
        $this->user = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $this->user->id, 'name' => 'Test Tenant']);

        // Create Plan and Subscription
        $plan = Plan::firstOrCreate(['slug' => 'pro'], [
            'name' => 'Pro',
            'description' => 'Test Plan',
            'base_price' => 10,
            'billing_cycle' => 'monthly',
            'limits' => ['employees' => 5],
            'is_active' => true,
        ]);

        Subscription::create([
            'tenant_id' => $tenant->id,
            'user_id' => $this->user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'price' => 0,
        ]);

        // Create company linked to tenant
        $this->company = TenantCompany::factory()->create([
            'tenant_id' => $tenant->id,
            'owner_id' => $this->user->id,
        ]);

        $this->company->members()->attach($this->user->id, [
            'role' => 'company_admin',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $this->user->update([
            'current_tenant_company_id' => $this->company->id,
            'current_scope' => 'company',
        ]);

        setPermissionsTeamId($this->company->id);
        $this->user->assignRole('company_admin');

        // Authenticate
        $this->actingAs($this->user);

        $this->customer = Customer::factory()->create([
            'tenant_company_id' => $this->company->id,
        ]);

        // Create statuses
        QuotationStatus::create([
            'name' => 'Draft',
            'tenant_company_id' => $this->company->id,
            'color' => 'gray',
            'is_default' => true,
        ]);

        QuotationStatus::create([
            'name' => 'Active',
            'tenant_company_id' => $this->company->id,
            'color' => 'green',
            'is_default' => false,
        ]);

        $this->quotation = Quotation::create([
            'tenant_company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'quotation_no' => 'QT-REV-TEST',
            'reference_no' => 'QT-REV-TEST',
            'status_id' => QuotationStatus::where('name', 'Draft')->first()->id,
            'ship_to' => 'Test Ship To',
        ]);

        // Create active revision 1
        $this->revision1 = QuotationRevision::create([
            'quotation_id' => $this->quotation->id,
            'revision_no' => 'R00',
            'date' => now()->format('Y-m-d'),
            'validity' => now()->addDays(15)->format('Y-m-d'),
            'type' => 'normal',
            'currency' => 'USD',
            'exchange_rate' => 100,
            'subtotal' => 1000,
            'total' => 1000,
            'saved_as' => 'draft',
            'is_active' => true,
            'created_by' => $this->user->id,
            'terms_conditions' => 'Terms and conditions',
        ]);

        // Create inactive revision 2
        $this->revision2 = QuotationRevision::create([
            'quotation_id' => $this->quotation->id,
            'revision_no' => 'R01',
            'date' => now()->format('Y-m-d'),
            'validity' => now()->addDays(15)->format('Y-m-d'),
            'type' => 'normal',
            'currency' => 'USD',
            'exchange_rate' => 100,
            'subtotal' => 1200,
            'total' => 1200,
            'saved_as' => 'draft',
            'is_active' => false,
            'created_by' => $this->user->id,
            'terms_conditions' => 'Terms and conditions',
        ]);
    }

    /** @test */
    public function it_can_activate_a_revision()
    {
        $response = $this->get(route('tenant.quotations.revisions.activate', $this->revision2->id));

        $response->assertRedirect(route('tenant.quotations.edit', $this->quotation->id));
        $response->assertSessionHas('success');

        // Check DB
        $this->assertDatabaseHas('quotation_revisions', [
            'id' => $this->revision1->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('quotation_revisions', [
            'id' => $this->revision2->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_updates_quotation_status_to_active_if_revision_saved_as_quotation()
    {
        // Update revision 2 to be saved as 'quotation'
        $this->revision2->update(['saved_as' => 'quotation']);

        $response = $this->get(route('tenant.quotations.revisions.activate', $this->revision2->id));

        $response->assertRedirect(route('tenant.quotations.edit', $this->quotation->id));

        $this->assertDatabaseHas('quotations', [
            'id' => $this->quotation->id,
            'status_id' => QuotationStatus::where('name', 'Active')->first()->id,
        ]);
    }
}
