<?php

namespace Database\Seeders;

use App\Models\Expense;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Expense::truncate();

        $faker = Faker::create();

        $expenseTypes = ['transport', 'phone', 'others', 'food'];

        // We will create 100 expense entries
        for ($i = 0; $i < 100; $i++) {

            // Generate a random date from 18 months ago until today.
            // This wide range ensures that all your filters (last year, last 6 months, etc.)
            // will have data to show.
            $date = $faker->dateTimeBetween('-18 months', 'now');

            // To ensure we have data for "Today" and "Last 7 days",
            // let's force a few entries to be very recent.
            if ($i < 5) { // First 5 entries will be in the last week
                $date = $faker->dateTimeBetween('-6 days', 'now');
            }
            if ($i === 0) { // The very first entry will be for today
                $date = Carbon::now();
            }

            Expense::create([
                'user_id' => '1',
                // Randomly pick one of the defined expense types
                'type' => $expenseTypes[array_rand($expenseTypes)],

                // Generate a realistic random amount (e.g., between $5.00 and $250.00)
                'amount' => $faker->randomFloat(2, 5, 250),

                'date' => $date,

                // Optionally add remarks to about 1 in 4 entries
                'remarks' => (rand(0, 3) === 0) ? $faker->sentence() : null,

                // We are not seeding files, so voucher will be null
                'voucher' => null,

                // If you have a user relationship, uncomment and set a user ID
                // 'user_id' => 1,
            ]);
        }
    }
}
