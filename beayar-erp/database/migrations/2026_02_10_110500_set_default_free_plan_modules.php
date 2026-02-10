<?php

use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set default module access for Free Plan
        $freePlan = Plan::where('slug', 'free')->first();
        if ($freePlan) {
            $freePlan->update([
                'module_access' => ['basic_crm', 'quotations', 'challans', 'billing', 'finance'],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse data update
    }
};
