<?php

namespace App\Console\Commands;

use App\Models\Quotation;
use App\Models\User;
use Illuminate\Console\Command;

class VerifyMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify data integrity after migration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running Verification Checks...');

        $userCount = User::count();
        $this->info("Total Users: {$userCount}");

        $quoteCount = Quotation::count();
        $this->info("Total Quotations: {$quoteCount}");

        // Add more specific checks (e.g. sum of revenue vs source)

        $this->info('Verification Completed.');
    }
}
