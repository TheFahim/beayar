<?php

namespace Tests\Feature\Dashboard;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class DashboardConversionRateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_conversion_rate_calculation_excludes_child_bills()
    {
        // 1. Create a user
        $user = User::factory()->create();
        $this->actingAs($user);

        $customer = Customer::factory()->create();

        // 2. Create Quotation 1 (Current Month)
        $quotation1 = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => Carbon::now(),
        ]);
        QuotationRevision::factory()->create([
            'quotation_id' => $quotation1->id,
            'created_by' => $user->id,
            'is_active' => true,
            'created_at' => Carbon::now(),
        ]);

        // 3. Create Quotation 2 (Current Month) - No bills
        $quotation2 = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => Carbon::now(),
        ]);
        QuotationRevision::factory()->create([
            'quotation_id' => $quotation2->id,
            'created_by' => $user->id,
            'is_active' => true,
            'created_at' => Carbon::now(),
        ]);

        // 4. Create Advance Bill (Parent) for Q1
        $bill1 = Bill::factory()->create([
            'quotation_id' => $quotation1->id,
            'bill_type' => 'advance',
            'parent_bill_id' => null,
            'created_at' => Carbon::now(),
        ]);

        // 5. Create Running Bill (Child) for Q1 (linked to Bill1)
        $bill2 = Bill::factory()->create([
            'quotation_id' => $quotation1->id,
            'bill_type' => 'running',
            'parent_bill_id' => $bill1->id,
            'created_at' => Carbon::now(),
        ]);

        // 6. Access Dashboard
        $response = $this->get(route('dashboard.index'));

        // 7. Verify Logic
        // Quotations: Q1, Q2 = 2
        // Bills: B1 (Parent) = 1. B2 (Child) should be excluded.
        // Conversion Rate: 1 / 2 * 100 = 50%

        $response->assertStatus(200);
        
        $stats = $response->viewData('conversionRateStats');
        
        // Current Month Stats
        $this->assertEquals(2, $stats['current_month']['quotation_count'], 'Quotation count should be 2');
        $this->assertEquals(1, $stats['current_month']['bill_count'], 'Bill count should be 1 (excluding child bill)');
        $this->assertEquals(50.0, $stats['current_month']['conversion_rate'], 'Conversion rate should be 50%');
    }
    
    public function test_conversion_rate_counts_multiple_parent_bills_separately()
    {
        // Test if multiple parent bills for one quotation count as multiple.
        
        $user = User::factory()->create();
        $this->actingAs($user);

        $customer = Customer::factory()->create();
        
        // Quotation 1
        $quotation1 = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => Carbon::now()
        ]);
        QuotationRevision::factory()->create([
            'quotation_id' => $quotation1->id,
            'created_by' => $user->id,
            'is_active' => true,
            'created_at' => Carbon::now(),
        ]);

        // Regular Bill 1 (Parent)
        Bill::factory()->create([
            'quotation_id' => $quotation1->id,
            'bill_type' => 'regular',
            'parent_bill_id' => null,
            'created_at' => Carbon::now(),
        ]);

        // Regular Bill 2 (Parent) - Another bill for same quotation
        Bill::factory()->create([
            'quotation_id' => $quotation1->id,
            'bill_type' => 'regular',
            'parent_bill_id' => null,
            'created_at' => Carbon::now(),
        ]);

        $response = $this->get(route('dashboard.index'));
        $stats = $response->viewData('conversionRateStats');

        // Expectation: 2 bills, 1 quotation. 200% conversion.
        $this->assertEquals(1, $stats['current_month']['quotation_count'], 'Quotation count should be 1');
        $this->assertEquals(2, $stats['current_month']['bill_count'], 'Bill count should be 2 (multiple parent bills)');
        $this->assertEquals(200.0, $stats['current_month']['conversion_rate'], 'Conversion rate should be 200%'); 
    }
    
    public function test_filters_bills_by_quotation_owner()
    {
        // Verify that bills are filtered based on whether the auth user owns the quotation
        
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);

        $customer = Customer::factory()->create();
        
        // Quotation owned by OTHER user
        $quotationOther = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => Carbon::now()
        ]);
        QuotationRevision::factory()->create([
            'quotation_id' => $quotationOther->id,
            'created_by' => $otherUser->id, // Other user
            'is_active' => true,
            'created_at' => Carbon::now(),
        ]);

        // Bill for OTHER user's quotation
        Bill::factory()->create([
            'quotation_id' => $quotationOther->id,
            'bill_type' => 'regular',
            'parent_bill_id' => null,
            'created_at' => Carbon::now(),
        ]);

        $response = $this->get(route('dashboard.index'));
        $stats = $response->viewData('conversionRateStats');

        // Expectation: 0 quotations (owned by user), 0 bills (owned by user's quotation)
        $this->assertEquals(0, $stats['current_month']['quotation_count']);
        $this->assertEquals(0, $stats['current_month']['bill_count']);
    }
}
