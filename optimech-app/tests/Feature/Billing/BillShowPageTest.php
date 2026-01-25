<?php

namespace Tests\Feature\Billing;

use App\Models\Bill;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillShowPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_page_renders_for_advance_bill(): void
    {
        $user = User::factory()->create(['username' => 'developer']);
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
            'invoice_no' => 'ADV-SHOW-001',
            'bill_date' => now()->format('Y-m-d'),
            'bill_type' => 'advance',
            'status' => 'issued',
            'total_amount' => 1000.00,
            'bill_percentage' => 50,
            'bill_amount' => 500.00,
            'due' => 500.00,
            'notes' => '',
        ]);

        $response = $this->get(route('bills.show', $advance));
        $response->assertStatus(200);
        $response->assertSee('Bill');
        $response->assertSee('Advance');
    }

    public function test_show_page_renders_for_regular_bill_without_items(): void
    {
        $user = User::factory()->create(['username' => 'developer']);
        $this->actingAs($user);

        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $quotation = Quotation::factory()->create(['customer_id' => $customer->id]);

        $regular = Bill::create([
            'quotation_id' => $quotation->id,
            'invoice_no' => 'REG-SHOW-001',
            'bill_date' => now()->format('Y-m-d'),
            'bill_type' => 'regular',
            'status' => 'issued',
            'total_amount' => 0,
            'bill_amount' => 0,
            'due' => 0,
            'notes' => '',
        ]);

        $response = $this->get(route('bills.show', $regular));
        $response->assertStatus(200);
        $response->assertSee('Bill');
        $response->assertSee('Regular');
    }
}
