<?php

namespace Tests\Feature\Dashboard;

use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationRevision;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserQuotationStatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin']);
    }

    public function test_non_admin_user_cannot_access_user_quotation_stats(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson(route('api.user.quotation.stats'));

        $response->assertForbidden();
    }

    public function test_admin_user_can_access_user_quotation_stats(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $response = $this->getJson(route('api.user.quotation.stats'));

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'total_count',
            'total_value',
            'users',
        ]);
    }

    public function test_returns_correct_user_quotation_counts(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $user1 = User::factory()->create(['name' => 'User One']);
        $user2 = User::factory()->create(['name' => 'User Two']);
        $customer = Customer::factory()->create();

        // Create 3 quotations for user1
        for ($i = 0; $i < 3; $i++) {
            $quotation = Quotation::factory()->create([
                'customer_id' => $customer->id,
                'created_at' => Carbon::now(),
            ]);
            QuotationRevision::factory()->create([
                'quotation_id' => $quotation->id,
                'created_by' => $user1->id,
                'is_active' => true,
                'total' => 1000,
            ]);
        }

        // Create 2 quotations for user2
        for ($i = 0; $i < 2; $i++) {
            $quotation = Quotation::factory()->create([
                'customer_id' => $customer->id,
                'created_at' => Carbon::now(),
            ]);
            QuotationRevision::factory()->create([
                'quotation_id' => $quotation->id,
                'created_by' => $user2->id,
                'is_active' => true,
                'total' => 2000,
            ]);
        }

        $response = $this->getJson(route('api.user.quotation.stats', ['filter' => 'this_month']));

        $response->assertSuccessful();
        $data = $response->json();

        $this->assertEquals(5, $data['total_count']);
        $this->assertCount(2, $data['users']);

        // Users should be ordered by quotation count (descending)
        $this->assertEquals('User One', $data['users'][0]['user_name']);
        $this->assertEquals(3, $data['users'][0]['quotation_count']);
        $this->assertEquals('User Two', $data['users'][1]['user_name']);
        $this->assertEquals(2, $data['users'][1]['quotation_count']);
    }

    public function test_this_month_filter_only_returns_current_month_quotations(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $user1 = User::factory()->create();
        $customer = Customer::factory()->create();

        // Create quotation this month (middle of the month to avoid edge cases)
        $quotation1 = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => Carbon::now()->startOfMonth()->addDays(5),
        ]);
        QuotationRevision::factory()->create([
            'quotation_id' => $quotation1->id,
            'created_by' => $user1->id,
            'is_active' => true,
        ]);

        // Create quotation 2 months ago to clearly avoid month boundary issues
        $quotation2 = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => Carbon::now()->subMonths(2)->startOfMonth(),
        ]);
        QuotationRevision::factory()->create([
            'quotation_id' => $quotation2->id,
            'created_by' => $user1->id,
            'is_active' => true,
        ]);

        $response = $this->getJson(route('api.user.quotation.stats', ['filter' => 'this_month']));

        $response->assertSuccessful();
        $data = $response->json();

        $this->assertEquals(1, $data['total_count']);
    }

    public function test_this_year_filter_returns_current_year_quotations(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $user1 = User::factory()->create();
        $customer = Customer::factory()->create();

        // Create quotation this year
        $quotation1 = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => Carbon::now()->startOfYear(),
        ]);
        QuotationRevision::factory()->create([
            'quotation_id' => $quotation1->id,
            'created_by' => $user1->id,
            'is_active' => true,
        ]);

        // Create quotation last year
        $quotation2 = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => Carbon::now()->subYear(),
        ]);
        QuotationRevision::factory()->create([
            'quotation_id' => $quotation2->id,
            'created_by' => $user1->id,
            'is_active' => true,
        ]);

        $response = $this->getJson(route('api.user.quotation.stats', ['filter' => 'this_year']));

        $response->assertSuccessful();
        $data = $response->json();

        $this->assertEquals(1, $data['total_count']);
    }

    public function test_all_filter_returns_all_quotations(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $user1 = User::factory()->create();
        $customer = Customer::factory()->create();

        // Create quotation this year
        $quotation1 = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => Carbon::now(),
        ]);
        QuotationRevision::factory()->create([
            'quotation_id' => $quotation1->id,
            'created_by' => $user1->id,
            'is_active' => true,
        ]);

        // Create quotation last year
        $quotation2 = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => Carbon::now()->subYear(),
        ]);
        QuotationRevision::factory()->create([
            'quotation_id' => $quotation2->id,
            'created_by' => $user1->id,
            'is_active' => true,
        ]);

        $response = $this->getJson(route('api.user.quotation.stats', ['filter' => 'all']));

        $response->assertSuccessful();
        $data = $response->json();

        $this->assertEquals(2, $data['total_count']);
    }

    public function test_invalid_filter_defaults_to_this_month(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $response = $this->getJson(route('api.user.quotation.stats', ['filter' => 'invalid_filter']));

        $response->assertSuccessful();
    }

    public function test_only_counts_active_revisions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $user1 = User::factory()->create();
        $customer = Customer::factory()->create();

        $quotation = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => Carbon::now(),
        ]);

        // Create inactive revision (should not be counted)
        QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'created_by' => $user1->id,
            'is_active' => false,
        ]);

        $response = $this->getJson(route('api.user.quotation.stats', ['filter' => 'all']));

        $response->assertSuccessful();
        $data = $response->json();

        $this->assertEquals(0, $data['total_count']);
    }
}
