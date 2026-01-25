<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Challan;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\ReceivedBill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuotationSeeder extends Seeder
{
    /**
     * The Faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->faker = \Faker\Factory::create();

        $user = User::first() ?? User::factory()->create();

        DB::beginTransaction();

        try {
            // Use factory's 'for' syntax to create 100 quotations
            for ($i = 0; $i < 100; $i++) {

                // 1. Create a Customer
                $customer = Customer::factory()->create();

                // 2. Create a Quotation
                $quotation = Quotation::factory()->create([
                    'customer_id' => $customer->id,
                    'user_id' => $user->id,
                    'terms_conditions' => $this->faker->paragraph,
                ]);

                // 3. Create Products for the Quotation
                $products = Product::factory()->count(rand(1, 10))->make();
                $subtotal = 0;
                $productsData = [];

                foreach ($products as $product) {
                    $amount = $product->price * $product->quantity;
                    $subtotal += $amount;
                    $productsData[] = [
                        'quotation_id' => $quotation->id,
                        'name' => $product->name,
                        'unit' => $product->unit,
                        'price' => $product->price,
                        'quantity' => $product->quantity,
                        'amount' => $amount,
                        'specs' => $product->specs,
                        'remarks' => $product->remarks,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DB::table('products')->insert($productsData);

                // 4. Calculate final total and update the quotation
                $vatRate = ['10', '15'][array_rand(['10', '15'])];
                $vatAmount = ($subtotal * $vatRate) / 100;
                $total = $subtotal + $vatAmount;

                $quotation->update([
                    'subtotal' => $subtotal,
                    'vat' => $vatRate,
                    'total' => $total,
                ]);

                // 5. Create a Challan for the Quotation
                $challan = Challan::factory()->create(['quotation_id' => $quotation->id]);

                // 6. Create a Bill for the Challan
                $bill = Bill::factory()->create([
                    'challan_id' => $challan->id,
                    'payable' => $quotation->total,
                    'due' => $quotation->total, // Initially, due is the full amount
                ]);

                // 7. Simulate Bill Payments (ReceivedBill records)
                $totalPaid = 0;
                // Create between 0 to 3 payment transactions for this bill
                if ($bill->payable > 0) {
                    for ($j = 0; $j < rand(0, 3); $j++) {
                        $remainingDue = $bill->payable - $totalPaid;
                        if ($remainingDue <= 0) {
                            break;
                        } // Stop if bill is fully paid

                        // Pay a random amount, but not more than what is due
                        $paymentAmount = $this->faker->randomFloat(2, 1, $remainingDue);

                        ReceivedBill::factory()->create([
                            'bill_id' => $bill->id,
                            'amount' => $paymentAmount,
                        ]);
                        $totalPaid += $paymentAmount;
                    }
                }

                // 8. Update the bill's paid and due amounts after payments
                if ($totalPaid > 0) {
                    $bill->update([
                        'paid' => $totalPaid,
                        'due' => $bill->payable - $totalPaid,
                    ]);
                }
            }

            DB::commit();
            $this->command->info('Successfully seeded 100 quotations with challans, bills, and payments.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Database seeding failed: '.$e->getMessage().' on line '.$e->getLine().' in '.$e->getFile());
            $this->command->error('An error occurred during seeding. Check logs for details.');
        }

    }
}
