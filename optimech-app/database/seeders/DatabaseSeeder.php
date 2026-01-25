<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use App\Models\Specification;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // $this->call([
        //     ExpenseSeeder::class
        // ]);
        // Create the 'admin' and 'user' roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // Create the user
        $user = User::create([
            'name' => 'The Developer',
            'username' => 'developer',
            'password' => bcrypt('h5o3iamm2f'),
        ]);

        // Assign the 'admin' role to the newly created user
        $user->assignRole($adminRole);

        // Create additional users for foreign key relationships
        $users = User::factory(5)->create();

        // Assign roles randomly to factory users
        foreach ($users as $factoryUser) {
            $randomRole = fake()->randomElement([$adminRole, $userRole]);
            $factoryUser->assignRole($randomRole);
        }

        // Create customers for quotations;
        $customers = Customer::factory(10)->create();

        // Create products for quotation products
        $products = Product::factory(20)->create();

        // Create specifications for each product (1-3 specifications per product)
        foreach ($products as $product) {
            $specCount = fake()->numberBetween(1, 3);
            Specification::factory($specCount)->create([
                'product_id' => $product->id,
            ]);
        }

        // Create 20 quotations
        // $quotations = Quotation::factory(20)->create([
        //     'customer_id' => function () use ($customers) {
        //         return $customers->random()->id;
        //     }
        // ]);

        // // Create 30 quotation revisions
        // $quotationRevisions = QuotationRevision::factory(30)->create([
        //     'quotation_id' => function () use ($quotations) {
        //         return $quotations->random()->id;
        //     },
        //     'created_by' => function () use ($users) {
        //         return $users->random()->id;
        //     },
        //     'updated_by' => function () use ($users) {
        //         return $users->random()->id;
        //     }
        // ]);

        // // Create 50 quotation products
        // QuotationProduct::factory(50)->create([
        //     'product_id' => function () use ($products) {
        //         return $products->random()->id;
        //     },
        //     'quotation_revision_id' => function () use ($quotationRevisions) {
        //         return $quotationRevisions->random()->id;
        //     }
        // ]);

    }
}
