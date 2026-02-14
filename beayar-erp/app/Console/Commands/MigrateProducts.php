<?php

namespace App\Console\Commands;

use App\Models\TenantCompany;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import product catalogs scoped by company';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Product Migration...');

        $companies = TenantCompany::all();

        foreach ($companies as $company) {
            // Simplified logic: In reality, we'd link companies back to source IDs
            // For now, let's assume we fetch all products from source and assign to first company found matching email
            // This is a placeholder for the actual mapping logic which requires a 'source_id' column on TenantCompany

            // Example for Optimech
            // $products = DB::connection('optimech_db')->table('products')->where('company_id', $company->source_id)->get();
            // foreach($products as $prod) { ... }

            $this->info("Processed products for company: {$company->name}");
        }

        $this->info('Product Migration Completed (Placeholder Logic)!');
    }
}
