<?php

namespace Database\Seeders;

use App\Models\BrandOrigin;
use App\Models\TenantCompany;
use Illuminate\Database\Seeder;

class BrandOriginSeeder extends Seeder
{
    public function run(): void
    {
        $tenantCompanies = TenantCompany::all();

        if ($tenantCompanies->isEmpty()) {
            return;
        }

        $origins = [
            ['name' => 'USA', 'country' => 'United States'],
            ['name' => 'China', 'country' => 'China'],
            ['name' => 'Germany', 'country' => 'Germany'],
            ['name' => 'Japan', 'country' => 'Japan'],
            ['name' => 'UK', 'country' => 'United Kingdom'],
            ['name' => 'Local', 'country' => 'Bangladesh'],
        ];

        foreach ($tenantCompanies as $company) {
            foreach ($origins as $origin) {
                // Check if already exists for this company
                $exists = BrandOrigin::where('tenant_company_id', $company->id)
                    ->where('name', $origin['name'])
                    ->exists();

                if (! $exists) {
                    BrandOrigin::create(array_merge($origin, [
                        'tenant_company_id' => $company->id,
                    ]));
                }
            }
        }
    }
}
