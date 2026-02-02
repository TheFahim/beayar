<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerCompany;
use App\Models\UserCompany;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have at least one UserCompany to attach customers to
        $userCompanies = UserCompany::all();
        
        if ($userCompanies->isEmpty()) {
            $userCompanies = UserCompany::factory(5)->create();
        }

        foreach ($userCompanies as $company) {
            // Create Customer Companies for this User Company
            $customerCompanies = CustomerCompany::factory(3)->create([
                'user_company_id' => $company->id,
            ]);

            foreach ($customerCompanies as $customerCompany) {
                // Create Customers for this Customer Company and User Company
                Customer::factory(5)->create([
                    'user_company_id' => $company->id,
                    'customer_company_id' => $customerCompany->id,
                ]);
            }
        }
    }
}
