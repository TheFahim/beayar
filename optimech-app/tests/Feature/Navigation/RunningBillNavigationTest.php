<?php

namespace Tests\Feature\Navigation;

use App\Models\Bill;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RunningBillNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_running_button_navigates_to_running_view(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $quotation = Quotation::factory()->create(['customer_id' => $customer->id]);
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
            'saved_as' => 'quotation',
        ]);

        $advance = Bill::create([
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'invoice_no' => 'ADV-NAV-001',
            'bill_date' => now()->format('Y-m-d'),
            'bill_type' => 'advance',
            'status' => 'issued',
            'total_amount' => 1000.00,
            'bill_percentage' => 50,
            'bill_amount' => 500.00,
            'due' => 500.00,
            'notes' => '',
        ]);

        $response = $this->get(route('bills.create', [
            'quotation_id' => $quotation->id,
            'parent_bill_id' => $advance->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.bills.create-running');
    }
}
