<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Inventory Management',
                'slug' => 'inventory',
                'price' => 10.00,
                'description' => 'Track stock levels and movements.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Accounting',
                'slug' => 'accounting',
                'price' => 20.00,
                'description' => 'Advanced financial reporting.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'HRM',
                'slug' => 'hrm',
                'price' => 15.00,
                'description' => 'Human Resource Management.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('modules')->insert($modules);
    }
}
