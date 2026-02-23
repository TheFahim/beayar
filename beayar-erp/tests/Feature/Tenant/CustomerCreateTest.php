<?php

namespace Tests\Feature\Tenant;

use App\Http\Middleware\CheckSubscriptionLimits;
use App\Http\Middleware\CheckUserIsActive;
use App\Http\Middleware\EnsureOnboardingComplete;
use App\Http\Middleware\EnsureOperationalCompany;
use App\Models\Customer;
use App\Models\CustomerCompany;
use App\Models\TenantCompany;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerCreateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $tenantCompany;
    protected $customerCompany;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->withoutMiddleware([
            CheckUserIsActive::class,
            EnsureOnboardingComplete::class,
        ]);
        
        // Setup Tenant and User
        $this->tenantCompany = TenantCompany::factory()->create();
        $this->user = User::factory()->create([
            'current_tenant_company_id' => $this->tenantCompany->id
        ]);
        
        // Set the team id for permission scoping
        setPermissionsTeamId($this->tenantCompany->id);

        // Setup Permissions
        // If roles are team-specific, we might need to pass the team id. 
        // Assuming roles are global or we just need to assign it.
        // The error suggests model_has_roles needs tenant_company_id.
        // Spatie's assignRole should pick it up if getPermissionsTeamId is working on User,
        // OR if we use setPermissionsTeamId.
        
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $permission = Permission::create(['name' => 'create_customers', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        
        $this->user->assignRole($role);
        
        $this->actingAs($this->user);

        // Create a Customer Company
        $this->customerCompany = CustomerCompany::factory()->create([
            'tenant_company_id' => $this->tenantCompany->id
        ]);
    }

    /** @test */
    public function it_can_create_customer_via_ajax_and_return_json()
    {
        $data = [
            'customer_company_id' => $this->customerCompany->id,
            'customer_name' => 'Test Customer AJAX',
            'customer_no' => 'CUST-AJAX-001',
            'email' => 'ajax@example.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'attention' => 'Mr. Ajax',
            'designation' => 'Manager',
            'department' => 'IT',
        ];

        $response = $this->postJson(route('tenant.customers.store'), $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Customer created successfully.',
            ])
            ->assertJsonPath('customer.name', 'Test Customer AJAX')
            ->assertJsonPath('customer.designation', 'Manager');

        $this->assertDatabaseHas('customers', [
            'name' => 'Test Customer AJAX',
            'email' => 'ajax@example.com',
            'designation' => 'Manager',
            'department' => 'IT',
        ]);
    }

    /** @test */
    public function it_redirects_on_normal_request()
    {
        $data = [
            'customer_company_id' => $this->customerCompany->id,
            'customer_name' => 'Test Customer Normal',
            'customer_no' => 'CUST-NORM-001',
            'address' => 'Normal St',
        ];

        $response = $this->post(route('tenant.customers.store'), $data);

        $response->assertRedirect(route('tenant.customers.index'));
        
        $this->assertDatabaseHas('customers', ['name' => 'Test Customer Normal']);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->postJson(route('tenant.customers.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_company_id', 'customer_name', 'customer_no']);
    }
}
