<?php

namespace Tests\Feature\Tenant;

use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationRevision;
use App\Models\QuotationStatus;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class QuotationRevisionActivationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $company;
    protected $customer;
    protected $quotation;
    protected $revision1;
    protected $revision2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create user and company
        $this->company = UserCompany::factory()->create();
        $this->user = User::factory()->create([
            'current_user_company_id' => $this->company->id,
        ]);

        // Authenticate
        $this->actingAs($this->user);

        $this->customer = Customer::factory()->create([
            'user_company_id' => $this->company->id,
        ]);

        // Create statuses
        QuotationStatus::create([
            'name' => 'Draft',
            'user_company_id' => $this->company->id,
            'color' => 'gray',
            'is_default' => true,
        ]);

        QuotationStatus::create([
            'name' => 'Active',
            'user_company_id' => $this->company->id,
            'color' => 'green',
            'is_default' => false,
        ]);

        $this->quotation = Quotation::create([
            'user_company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'quotation_no' => 'QT-REV-TEST',
            'reference_no' => 'QT-REV-TEST',
            'status_id' => QuotationStatus::where('name', 'Draft')->first()->id,
        ]);

        // Create active revision 1
        $this->revision1 = QuotationRevision::create([
            'quotation_id' => $this->quotation->id,
            'revision_no' => 'R00',
            'date' => now()->format('Y-m-d'),
            'validity' => now()->addDays(15)->format('Y-m-d'),
            'type' => 'normal',
            'currency' => 'USD',
            'exchange_rate' => 100,
            'subtotal' => 1000,
            'total' => 1000,
            'saved_as' => 'draft',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        // Create inactive revision 2
        $this->revision2 = QuotationRevision::create([
            'quotation_id' => $this->quotation->id,
            'revision_no' => 'R01',
            'date' => now()->format('Y-m-d'),
            'validity' => now()->addDays(15)->format('Y-m-d'),
            'type' => 'normal',
            'currency' => 'USD',
            'exchange_rate' => 100,
            'subtotal' => 1200,
            'total' => 1200,
            'saved_as' => 'draft',
            'is_active' => false,
            'created_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_can_activate_a_revision()
    {
        $response = $this->get(route('tenant.quotations.revisions.activate', $this->revision2->id));

        $response->assertRedirect(route('tenant.quotations.edit', $this->quotation->id));
        $response->assertSessionHas('success');

        // Check DB
        $this->assertDatabaseHas('quotation_revisions', [
            'id' => $this->revision1->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('quotation_revisions', [
            'id' => $this->revision2->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_updates_quotation_status_to_active_if_revision_saved_as_quotation()
    {
        // Update revision 2 to be saved as 'quotation'
        $this->revision2->update(['saved_as' => 'quotation']);

        $response = $this->get(route('tenant.quotations.revisions.activate', $this->revision2->id));

        $response->assertRedirect(route('tenant.quotations.edit', $this->quotation->id));
        
        $this->assertDatabaseHas('quotations', [
            'id' => $this->quotation->id,
            'status_id' => QuotationStatus::where('name', 'Active')->first()->id,
        ]);
    }
}
