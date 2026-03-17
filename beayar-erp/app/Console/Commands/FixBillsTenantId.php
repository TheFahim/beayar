<?php

namespace App\Console\Commands;

use App\Models\Bill;
use Illuminate\Console\Command;

class FixBillsTenantId extends Command
{
    protected $signature = 'bills:fix-tenant-id';
    protected $description = 'Fix bills with NULL tenant_company_id by inferring from related quotations';

    public function handle()
    {
        $this->info('Finding bills with NULL tenant_company_id...');

        // Find bills with NULL tenant_company_id
        $bills = Bill::whereNull('tenant_company_id')->get();

        if ($bills->isEmpty()) {
            $this->info('No bills with NULL tenant_company_id found.');
            return 0;
        }

        $this->info("Found {$bills->count()} bills with NULL tenant_company_id.");

        $fixed = 0;
        $failed = 0;

        foreach ($bills as $bill) {
            // Try to get tenant_company_id from the related quotation
            $quotation = $bill->quotation;

            if ($quotation && $quotation->tenant_company_id) {
                $bill->tenant_company_id = $quotation->tenant_company_id;
                $bill->save();
                $fixed++;
                $this->line("Fixed bill ID {$bill->id} with tenant_company_id {$bill->tenant_company_id}");
            } else {
                $failed++;
                $this->warn("Could not fix bill ID {$bill->id} - no related quotation with tenant_company_id");
            }
        }

        $this->info("Fixed {$fixed} bills. Failed to fix {$failed} bills.");

        return 0;
    }
}
