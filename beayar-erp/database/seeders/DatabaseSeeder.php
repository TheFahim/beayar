<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlansSeeder::class,
            ModuleSeeder::class,
            QuotationStatusSeeder::class,
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            CustomerSeeder::class,
            ProductSeeder::class,
            BrandOriginSeeder::class,
            QuotationSeeder::class,
        ]);
    }
}
