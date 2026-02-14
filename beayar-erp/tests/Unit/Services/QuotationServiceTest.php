<?php

namespace Tests\Unit\Services;

use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationStatus;
use App\Models\User;
use App\Models\TenantCompany;
use App\Services\Tenant\QuotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected $user;

    protected $company;

    protected $customer;

    protected $status;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QuotationService;

        // Setup Tenant Context
        $this->user = User::factory()->create([
            'current_tenant_company_id' => null,
            'current_scope' => 'company',
        ]);

        $this->company = TenantCompany::create([
            'name' => 'Test Company',
            'owner_id' => $this->user->id,
            'email' => 'company@test.com',
        ]);

        $this->user->update(['current_tenant_company_id' => $this->company->id]);

        $customerCompany = \App\Models\CustomerCompany::create([
            'tenant_company_id' => $this->company->id,
            'name' => 'Test Customer Company',
        ]);

        $this->customer = Customer::create([
            'tenant_company_id' => $this->company->id,
            'customer_company_id' => $customerCompany->id,
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
        ]);

        $this->status = QuotationStatus::create([
            'name' => 'Draft',
            'tenant_company_id' => $this->company->id,
        ]);
    }

    public function test_create_quotation_creates_quotation_and_revision()
    {
        $data = [
            'customer_id' => $this->customer->id,
            'status_id' => $this->status->id,
            'po_no' => 'PO-123',
            'subtotal' => 1000,
            'total' => 1100,
            'products' => [
                [
                    'product_name' => 'Test Product',
                    'quantity' => 1,
                    'unit_price' => 1000,
                    'total' => 1000,
                ],
            ],
        ];

        $quotation = $this->service->createQuotation($this->user, $data);

        $this->assertInstanceOf(Quotation::class, $quotation);
        $this->assertEquals($this->user->current_tenant_company_id, $quotation->tenant_company_id);
        $this->assertEquals($data['po_no'], $quotation->po_no);

        // Check Revision
        $this->assertCount(1, $quotation->revisions);
        $revision = $quotation->revisions->first();
        $this->assertTrue((bool) $revision->is_active);
        $this->assertEquals(1100, $revision->total);

        // Check Products
        $this->assertCount(1, $revision->products);
        $this->assertEquals('Test Product', $revision->products->first()->product_name);
    }

    public function test_create_revision_deactivates_old_revisions()
    {
        // Create initial quotation
        $data = [
            'customer_id' => $this->customer->id,
            'status_id' => $this->status->id,
            'subtotal' => 100,
            'total' => 100,
        ];
        $quotation = $this->service->createQuotation($this->user, $data);
        $oldRevision = $quotation->revisions->first();

        // Create new revision
        $newData = [
            'subtotal' => 200,
            'total' => 200,
            'revision_no' => 'R2',
        ];
        $newRevision = $this->service->createRevision($quotation, $newData, $this->user);

        // Refresh old revision
        $oldRevision->refresh();
        $quotation->refresh();

        $this->assertTrue((bool) $newRevision->is_active);
        $this->assertFalse((bool) $oldRevision->is_active);
        $this->assertCount(2, $quotation->revisions);
    }
}
