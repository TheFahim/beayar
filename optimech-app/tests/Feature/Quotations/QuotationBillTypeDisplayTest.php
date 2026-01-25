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

class QuotationBillTypeDisplayTest extends TestCase
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

    public function test_displays_no_bills_when_none_exist(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('quotations.index'));
        $response->assertStatus(200);
        $response->assertSee('No bills', false);
    }

    public function test_displays_advance_when_advance_exists(): void
    {
        $this->actingAs($this->user);

        Bill::create([
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'invoice_no' => 'ADV-SHOW-001',
            'bill_date' => now()->format('Y-m-d'),
            'bill_type' => 'advance',
            'status' => 'issued',
            'total_amount' => 1000.00,
            'notes' => '',
        ]);

        $response = $this->get(route('quotations.index'));
        $response->assertStatus(200);
        $response->assertSee('Advance', false);
    }

    public function test_displays_running_and_parent_advance(): void
    {
        $this->actingAs($this->user);

        $advance = Bill::create([
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'invoice_no' => 'ADV-PARENT-001',
            'bill_date' => now()->format('Y-m-d'),
            'bill_type' => 'advance',
            'status' => 'issued',
            'total_amount' => 1000.00,
            'notes' => '',
        ]);

        $running = Bill::create([
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'parent_bill_id' => $advance->id,
            'invoice_no' => 'RUN-CHILD-001',
            'bill_date' => now()->format('Y-m-d'),
            'bill_type' => 'running',
            'status' => 'issued',
            'total_amount' => 300.00,
            'notes' => '',
        ]);

        $response = $this->get(route('quotations.index'));
        $response->assertStatus(200);
        $response->assertSee('Running', false);
        $response->assertSee('Parent: Advance', false);
        $response->assertSee('Running → Advance', false);
    }
}
