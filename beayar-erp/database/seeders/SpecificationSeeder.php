<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Specification;
use Illuminate\Database\Seeder;

class SpecificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            // Check if product already has specifications to avoid duplication if run multiple times
            if ($product->specifications()->exists()) {
                continue;
            }

            Specification::factory(rand(1, 3))->create([
                'product_id' => $product->id,
            ]);
        }
    }
}
