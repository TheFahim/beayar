<?php

namespace Tests\Feature\Tenant;

use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\AbstractPaginator;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and company
        $this->user = User::factory()->create();
        $this->company = UserCompany::factory()->create([
            'owner_id' => $this->user->id
        ]);
        
        $this->user->update([
            'current_user_company_id' => $this->company->id
        ]);
    }

    public function test_modules_return_paginated_results()
    {
        $modules = [
            'tenant.quotations.index' => 'quotations',
            'tenant.bills.index' => 'bills',
            'tenant.challans.index' => 'challans',
            'tenant.received-bills.index' => 'receivedBills',
            'tenant.customers.index' => 'customers',
            'tenant.products.index' => 'products',
        ];

        foreach ($modules as $route => $viewKey) {
            $response = $this->actingAs($this->user)->get(route($route));

            $response->assertStatus(200);
            
            // Assert the view has the key
            $response->assertViewHas($viewKey);

            // Get the data from the view
            $data = $response->viewData($viewKey);

            // Assert it is a paginator
            $this->assertTrue(
                $data instanceof AbstractPaginator, 
                "The data for route {$route} (key: {$viewKey}) is not a paginator instance."
            );
        }
    }
}
