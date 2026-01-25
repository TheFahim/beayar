<?php

namespace Tests\Feature\Challan;

use App\Models\Challan;
use App\Models\ChallanProduct;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallanShowViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_challan_show_renders_with_remarks(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $quotation = Quotation::factory()->create(['customer_id' => $customer->id]);
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'type' => 'normal',
            'revision_no' => 'R00',
            'date' => now()->format('Y-m-d'),
            'validity' => now()->addDays(15)->format('Y-m-d'),
            'currency' => 'BDT',
            'exchange_rate' => 1,
            'subtotal' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'shipping' => 0,
            'vat_percentage' => 0,
            'vat_amount' => 0,
            'total' => 0,
            'saved_as' => 'quotation',
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $qp = QuotationProduct::factory()->create([
            'quotation_revision_id' => $revision->id,
            'quantity' => 3,
            'unit' => 'pcs',
        ]);

        $challan = Challan::create([
            'quotation_revision_id' => $revision->id,
            'challan_no' => 'CH-UI-001',
            'date' => now()->format('Y-m-d'),
        ]);

        ChallanProduct::create([
            'quotation_product_id' => $qp->id,
            'challan_id' => $challan->id,
            'quantity' => 2,
            'remarks' => 'Handle with care',
        ]);

        $resp = $this->get(route('challans.show', $challan));
        $resp->assertStatus(200);
        $resp->assertSee('CH: CH-UI-001');
        $resp->assertSee('Handle with care');
    }
}
