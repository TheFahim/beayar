<?php

namespace Tests\Feature;

use App\Models\Quotation;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_other_company_data()
    {
        // Setup Company A
        $userA = User::factory()->create([
            'current_user_company_id' => null,
            'current_scope' => 'company'
        ]);
        $companyA = UserCompany::create([
            'name' => 'Company A',
            'email' => 'a@test.com',
            'owner_id' => $userA->id
        ]);
        $userA->update(['current_user_company_id' => $companyA->id]);

        // Setup Company B
        $userB = User::factory()->create([
            'current_user_company_id' => null,
            'current_scope' => 'company'
        ]);
        $companyB = UserCompany::create([
            'name' => 'Company B',
            'email' => 'b@test.com',
            'owner_id' => $userB->id
        ]);
        $userB->update(['current_user_company_id' => $companyB->id]);

        // Create Customers
        $customerCompanyA = \App\Models\CustomerCompany::create([
            'user_company_id' => $companyA->id,
            'name' => 'Customer Company A'
        ]);
        $customerA = \App\Models\Customer::create([
            'customer_company_id' => $customerCompanyA->id,
            'name' => 'Customer A'
        ]);

        $customerCompanyB = \App\Models\CustomerCompany::create([
            'user_company_id' => $companyB->id,
            'name' => 'Customer Company B'
        ]);
        $customerB = \App\Models\Customer::create([
            'customer_company_id' => $customerCompanyB->id,
            'name' => 'Customer B'
        ]);

        $statusA = \App\Models\QuotationStatus::create(['name' => 'Draft', 'user_company_id' => $companyA->id]);
        $statusB = \App\Models\QuotationStatus::create(['name' => 'Draft', 'user_company_id' => $companyB->id]);

        // Create Quotation for Company A
        Quotation::create([
            'user_company_id' => $companyA->id,
            'customer_id' => $customerA->id,
            'user_id' => $userA->id,
            'status_id' => $statusA->id,
            'reference_no' => 'QT-A',
            'po_no' => 'PO-A'
        ]);

        // Create Quotation for Company B
        Quotation::create([
            'user_company_id' => $companyB->id,
            'customer_id' => $customerB->id,
            'user_id' => $userB->id,
            'status_id' => $statusB->id,
            'reference_no' => 'QT-B',
            'po_no' => 'PO-B'
        ]);

        // Act as User A
        $this->actingAs($userA);

        // Fetch Quotations
        $quotations = Quotation::all();

        // Assert
        $this->assertCount(1, $quotations);
        $this->assertEquals('QT-A', $quotations->first()->reference_no);
    }
}
