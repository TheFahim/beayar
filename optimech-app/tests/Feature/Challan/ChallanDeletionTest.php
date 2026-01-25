<?php

namespace Tests\Feature\Challan;

use App\Models\Bill;
use App\Models\Challan;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChallanDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Quotation $quotation;

    protected QuotationRevision $revision;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);

        $this->quotation = Quotation::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $this->revision = QuotationRevision::create([
            'quotation_id' => $this->quotation->id,
            'type' => 'normal',
            'revision_no' => 'REV-DEL-001',
            'date' => now()->format('Y-m-d'),
            'validity' => now()->addDays(30)->format('Y-m-d'),
            'currency' => 'BDT',
            'exchange_rate' => 1,
            'subtotal' => 1000.00,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'shipping' => 0,
            'vat_percentage' => 0,
            'vat_amount' => 0,
            'total' => 1000.00,
            'terms_conditions' => 'Terms',
            'saved_as' => 'quotation',
            'is_active' => true,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
    }

    public function test_can_delete_challan_without_bills(): void
    {
        $challan = Challan::create([
            'quotation_revision_id' => $this->revision->id,
            'challan_no' => 'CH-DEL-001',
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->delete(route('challans.destroy', $challan));

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('challans', ['id' => $challan->id]);
    }

    public function test_cannot_delete_challan_linked_to_bill(): void
    {
        $challan = Challan::create([
            'quotation_revision_id' => $this->revision->id,
            'challan_no' => 'CH-DEL-002',
            'date' => now()->format('Y-m-d'),
        ]);

        $billId = DB::table('bills')->insertGetId([
            'quotation_id' => $this->quotation->id,
            'quotation_revision_id' => $this->revision->id,
            'invoice_no' => 'INV-DEL-001',
            'bill_date' => now()->format('Y-m-d'),
            'payment_received_date' => now()->format('Y-m-d'),
            'bill_type' => 'regular',
            'total_amount' => 1000.00,
            'bill_percentage' => 100,
            'bill_amount' => 1000.00,
            'due' => 0,
            'shipping' => 0,
            'status' => 'issued',
            'notes' => 'Test bill',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $bill = Bill::findOrFail($billId);

        $bill->challans()->attach($challan->id);

        $this->assertDatabaseHas('bill_challans', [
            'bill_id' => $bill->id,
            'challan_id' => $challan->id,
        ]);

        $response = $this->delete(route('challans.destroy', $challan));

        $response->assertStatus(403);
        $this->assertDatabaseHas('challans', ['id' => $challan->id]);
    }
}
