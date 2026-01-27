<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:bills';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate bills and link to quotations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Bill Migration...');
        // Logic to import bills and link `quotation_id`
        $this->info('Bill Migration Completed!');
    }
}
