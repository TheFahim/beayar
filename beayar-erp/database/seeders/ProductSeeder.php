<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\UserCompany;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userCompanies = UserCompany::all();

        if ($userCompanies->isEmpty()) {
             $userCompanies = UserCompany::factory(5)->create();
        }

        foreach ($userCompanies as $company) {
            Product::factory(10)->create([
                'user_company_id' => $company->id,
            ]);
        }
    }
}
