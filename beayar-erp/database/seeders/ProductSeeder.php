<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\TenantCompany;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantCompanies = TenantCompany::all();

        if ($tenantCompanies->isEmpty()) {
            $tenantCompanies = TenantCompany::factory(5)->create();
        }

        foreach ($tenantCompanies as $company) {
            Product::factory(10)->create([
                'tenant_company_id' => $company->id,
            ]);
        }

        $this->call(SpecificationSeeder::class);
    }
}
