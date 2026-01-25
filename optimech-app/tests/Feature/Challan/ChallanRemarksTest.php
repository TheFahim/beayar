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

class ChallanRemarksTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Quotation $quotation;

    protected QuotationRevision $revision;

    protected QuotationProduct $qp;

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
            'revision_no' => 'REV-REM-001',
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

        $this->qp = QuotationProduct::factory()->create([
            'quotation_revision_id' => $this->revision->id,
            'quantity' => 5,
        ]);
    }

    public function test_store_challan_with_item_remarks_persists(): void
    {
        $payload = [
            'quotation_revision_id' => $this->revision->id,
            'date' => now()->format('d/m/Y'),
            'challan_no' => 'CH-REM-001',
            'po_no' => 'PO-REM-001',
            'items' => [
                [
                    'selected' => 1,
                    'quotation_product_id' => $this->qp->id,
                    'quantity' => 2,
                    'remarks' => 'Deliver to loading dock A',
                ],
            ],
        ];

        $response = $this->post(route('challans.store'), $payload);

        $response->assertStatus(302);
        $this->assertDatabaseHas('challans', ['challan_no' => 'CH-REM-001']);

        $challan = Challan::where('challan_no', 'CH-REM-001')->first();
        $this->assertNotNull($challan);

        $this->assertDatabaseHas('challan_products', [
            'challan_id' => $challan->id,
            'quotation_product_id' => $this->qp->id,
            'quantity' => 2,
            'remarks' => 'Deliver to loading dock A',
        ]);
    }

    public function test_update_challan_item_remarks_are_modified(): void
    {
        // Create initial challan via store to ensure proper state
        $createResp = $this->post(route('challans.store'), [
            'quotation_revision_id' => $this->revision->id,
            'date' => now()->format('d/m/Y'),
            'challan_no' => 'CH-REM-002',
            'po_no' => 'PO-REM-002',
            'items' => [
                [
                    'selected' => 1,
                    'quotation_product_id' => $this->qp->id,
                    'quantity' => 1,
                    'remarks' => 'Initial note',
                ],
            ],
        ]);
        $createResp->assertStatus(302);

        $challan = Challan::where('challan_no', 'CH-REM-002')->firstOrFail();

        $updateResp = $this->put(route('challans.update', $challan), [
            'quotation_revision_id' => $this->revision->id,
            'date' => now()->format('d/m/Y'),
            'challan_no' => 'CH-REM-002',
            'po_no' => $challan->revision->quotation->po_no ?? 'PO-REM-002',
            'items' => [
                [
                    'selected' => 1,
                    'quotation_product_id' => $this->qp->id,
                    'quantity' => 2,
                    'remarks' => 'Updated note',
                ],
            ],
        ]);
        $updateResp->assertStatus(302);

        $this->assertDatabaseHas('challan_products', [
            'challan_id' => $challan->id,
            'quotation_product_id' => $this->qp->id,
            'quantity' => 2,
            'remarks' => 'Updated note',
        ]);
    }

    public function test_validation_fails_when_remarks_exceed_limit(): void
    {
        $longRemarks = str_repeat('x', 1001);

        $response = $this->post(route('challans.store'), [
            'quotation_revision_id' => $this->revision->id,
            'date' => now()->format('d/m/Y'),
            'challan_no' => 'CH-REM-003',
            'po_no' => 'PO-REM-003',
            'items' => [
                [
                    'selected' => 1,
                    'quotation_product_id' => $this->qp->id,
                    'quantity' => 1,
                    'remarks' => $longRemarks,
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['items.0.remarks']);
        $this->assertDatabaseMissing('challans', ['challan_no' => 'CH-REM-003']);
    }

    public function test_api_returns_challan_product_with_remarks(): void
    {
        $challan = Challan::create([
            'quotation_revision_id' => $this->revision->id,
            'challan_no' => 'CH-REM-004',
            'date' => now()->format('Y-m-d'),
        ]);

        $cp = ChallanProduct::create([
            'quotation_product_id' => $this->qp->id,
            'challan_id' => $challan->id,
            'quantity' => 1,
            'remarks' => 'API exposure note',
        ]);

        $response = $this->get(route('challans.products', ['challan_ids' => [$challan->id]]));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $cp->id,
            'remarks' => 'API exposure note',
        ]);
    }
}
