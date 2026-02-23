<?php

namespace Tests\Feature\Tenant;

use App\Models\TenantCompany;
use App\Models\User;
use App\Models\CustomerCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use App\Http\Middleware\CheckUserIsActive;
use App\Http\Middleware\EnsureOnboardingComplete;
use App\Http\Middleware\CheckSubscriptionLimits;
use App\Http\Middleware\EnsureOperationalCompany;

class CompanyCreateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $tenantCompany;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->withoutMiddleware([
            CheckUserIsActive::class,
            EnsureOnboardingComplete::class,
            CheckSubscriptionLimits::class,
            EnsureOperationalCompany::class,
        ]);

        // Setup Tenant and User
        $this->tenantCompany = TenantCompany::factory()->create();
        $this->user = User::factory()->create([
            'current_tenant_company_id' => $this->tenantCompany->id
        ]);
        
        setPermissionsTeamId($this->tenantCompany->id);
        
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_create_company_via_ajax()
    {
        $data = [
            'name' => 'New Test Company',
            'company_code' => 'NTC',
            'email' => 'company@test.com',
            'phone' => '9876543210',
            'address' => '456 Company Lane',
        ];

        $response = $this->postJson(route('companies.store'), $data);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'New Test Company')
            ->assertJsonPath('company_code', 'NTC');

        $this->assertDatabaseHas('customer_companies', [
            'name' => 'New Test Company',
            'company_code' => 'NTC',
            'tenant_company_id' => $this->tenantCompany->id
        ]);
    }

    /** @test */
    public function it_validates_company_creation()
    {
        $response = $this->postJson(route('companies.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'address']);
    }
}
