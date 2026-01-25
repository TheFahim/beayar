<?php

namespace Tests\Feature\Quotations;

use App\Models\Bill;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationIndexAdvanceLockTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Quotation $quotation;

    protected QuotationRevision $revision;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $this->quotation = Quotation::factory()->create(['customer_id' => $customer->id]);
        $this->revision = QuotationRevision::factory()->create([
            'quotation_id' => $this->quotation->id,
            'is_active' => true,
            'saved_as' => 'quotation',
        ]);
    }

    public function test_index_shows_advance_converted_badge_and_hides_regular_bill_button(): void
    {
        $this->actingAs($this->user);

        Bill::create([
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'invoice_no' => 'ADV-LK-001',
            'bill_date' => now()->format('Y-m-d'),
            'bill_type' => 'advance',
            'status' => 'issued',
            'total_amount' => 1000.00,
            'bill_percentage' => 30,
            'bill_amount' => 300.00,
            'due' => 700.00,
            'shipping' => 0.00,
            'discount' => 0.00,
            'notes' => '',
        ]);

        $response = $this->get(route('quotations.index'));
        $response->assertStatus(200);
        $response->assertSee('Advance Converted', false);
        $response->assertDontSee('Create Regular Bill', false);
    }
}
