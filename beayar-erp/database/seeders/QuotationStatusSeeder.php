<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuotationStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Draft', 'color' => 'gray', 'is_default' => true],
            ['name' => 'Sent', 'color' => 'blue', 'is_default' => false],
            ['name' => 'Accepted', 'color' => 'green', 'is_default' => false],
            ['name' => 'Rejected', 'color' => 'red', 'is_default' => false],
            ['name' => 'Expired', 'color' => 'yellow', 'is_default' => false],
        ];

        foreach ($statuses as $status) {
            DB::table('quotation_statuses')->insert(array_merge($status, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
