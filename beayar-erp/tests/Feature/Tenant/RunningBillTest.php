<?php

namespace Tests\Feature\Tenant;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\CustomerCompany;
use App\Models\Quotation;
use App\Models\QuotationRevision;
use App\Models\QuotationStatus;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RunningBillTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $company;

    protected $quotation;

    protected $revision;

    protected $advanceBill;

    protected $status;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = UserCompany::factory()->create(['owner_id' => $this->user->id]);
        $this->user->update(['current_user_company_id' => $this->company->id]);

        $customerCompany = CustomerCompany::create([
            'user_company_id' => $this->company->id,
            'name' => 'Test Company',
            'email' => 'test@company.com',
            'phone' => '1234567890',
            'address' => 'Test Address',
        ]);

        $customer = Customer::create([
            'user_company_id' => $this->company->id,
            'customer_company_id' => $customerCompany->id,
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'phone' => '0987654321',
            'address' => 'Customer Address',
        ]);

        // Create QuotationStatus
        $this->status = QuotationStatus::create([
            'user_company_id' => $this->company->id,
            'name' => 'Approved',
            'color' => '#00FF00',
            'is_default' => true,
        ]);

        // Create Quotation manually
        $this->quotation = Quotation::create([
            'user_company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'reference_no' => 'QT-2024-TEST',
            'q_no' => '001',
            'subject' => 'Test Subject',
            'status_id' => $this->status->id,
        ]);

        // Create Revision manually
        $this->revision = QuotationRevision::create([
            'quotation_id' => $this->quotation->id,
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'is_active' => true,
            'saved_as' => 'quotation',
            'total' => 10000,
            'subtotal' => 10000,
            'vat' => 0,
            'discount' => 0,
            'date' => now(),
            'valid_until' => now()->addDays(30),
            'currency' => 'BDT',
        ]);

        // Create Advance Bill (Parent) manually
        $this->advanceBill = Bill::create([
            'user_company_id' => $this->company->id,
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'bill_type' => 'advance',
            'total_amount' => 10000,
            'bill_amount' => 10000,
            'bill_percentage' => 100,
            'due' => 10000,
            'invoice_no' => 'ADV-001',
            'bill_date' => now(),
            'status' => 'issued',
        ]);
    }

    public function test_can_view_create_running_bill_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('tenant.quotations.bill', [
                'quotation' => $this->quotation->id,
                'parent_bill_id' => $this->advanceBill->id,
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.bills.create-running');
        $response->assertSee('Create Running Bill');

        // Assert children are loaded
        $viewData = $response->viewData('parentBill');
        $this->assertTrue($viewData->relationLoaded('children'));
    }

    public function test_can_store_running_bill()
    {
        $response = $this->actingAs($this->user)
            ->post(route('tenant.quotations.bills.running.store', $this->quotation), [
                'bill_type' => 'running',
                'quotation_id' => $this->quotation->id,
                'parent_bill_id' => $this->advanceBill->id,
                'invoice_no' => 'RUN-001',
                'bill_date' => now()->format('d/m/Y'),
                'bill_amount' => 2000,
                'bill_percentage' => 20,
                'due' => 8000,
                'quotation_revision_id' => $this->revision->id,
            ]);

        $response->assertRedirect(route('tenant.bills.index'));
        $this->assertDatabaseHas('bills', [
            'invoice_no' => 'RUN-001',
            'bill_type' => 'running',
            'parent_bill_id' => $this->advanceBill->id,
            'bill_amount' => 2000,
        ]);
    }

    public function test_can_view_edit_running_bill_page()
    {
        $runningBill = Bill::create([
            'user_company_id' => $this->company->id,
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'parent_bill_id' => $this->advanceBill->id,
            'bill_type' => 'running',
            'total_amount' => 10000,
            'bill_amount' => 2000,
            'bill_percentage' => 20,
            'due' => 8000,
            'invoice_no' => 'RUN-001',
            'bill_date' => now(),
            'status' => 'issued',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('tenant.bills.edit', $runningBill));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.bills.edit-running');
    }

    public function test_can_update_running_bill()
    {
        $runningBill = Bill::create([
            'user_company_id' => $this->company->id,
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'parent_bill_id' => $this->advanceBill->id,
            'bill_type' => 'running',
            'total_amount' => 10000,
            'bill_amount' => 2000,
            'bill_percentage' => 20,
            'due' => 8000,
            'invoice_no' => 'RUN-001',
            'bill_date' => now(),
            'status' => 'issued',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('tenant.bills.running.update', $runningBill), [
                'invoice_no' => 'RUN-001-UPDATED',
                'bill_date' => now()->format('d/m/Y'),
                'bill_amount' => 3000,
                'bill_percentage' => 30,
                'due' => 7000,
            ]);

        $response->assertRedirect(route('tenant.bills.index'));
        $this->assertDatabaseHas('bills', [
            'id' => $runningBill->id,
            'invoice_no' => 'RUN-001-UPDATED',
            'bill_amount' => 3000,
        ]);
    }
}
