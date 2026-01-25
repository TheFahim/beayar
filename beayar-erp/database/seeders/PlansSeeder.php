<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'For freelancers and small startups.',
                'base_price' => 0.00,
                'billing_cycle' => 'monthly',
                'limits' => json_encode([
                    'sub_companies' => 1,
                    'quotations' => 20,
                    'employees' => 3,
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'For growing businesses.',
                'base_price' => 29.00,
                'billing_cycle' => 'monthly',
                'limits' => json_encode([
                    'sub_companies' => 5,
                    'quotations' => 100,
                    'employees' => 10,
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pro Plus',
                'slug' => 'pro-plus',
                'description' => 'For established enterprises.',
                'base_price' => 79.00,
                'billing_cycle' => 'monthly',
                'limits' => json_encode([
                    'sub_companies' => 15,
                    'quotations' => -1, // Unlimited
                    'employees' => 50,
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Custom',
                'slug' => 'custom',
                'description' => 'Tailored to your needs.',
                'base_price' => 0.00, // Calculated
                'billing_cycle' => 'monthly',
                'limits' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('plans')->insert($plans);
    }
}
