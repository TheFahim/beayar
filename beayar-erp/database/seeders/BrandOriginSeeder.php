<?php

namespace Database\Seeders;

use App\Models\BrandOrigin;
use App\Models\UserCompany;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandOriginSeeder extends Seeder
{
    public function run(): void
    {
        $userCompanies = UserCompany::all();

        if ($userCompanies->isEmpty()) {
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

        foreach ($userCompanies as $company) {
            foreach ($origins as $origin) {
                // Check if already exists for this company
                $exists = BrandOrigin::where('user_company_id', $company->id)
                    ->where('name', $origin['name'])
                    ->exists();

                if (!$exists) {
                    BrandOrigin::create(array_merge($origin, [
                        'user_company_id' => $company->id,
                    ]));
                }
            }
        }
    }
}
