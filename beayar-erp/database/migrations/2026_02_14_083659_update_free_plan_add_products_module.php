<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all dynamic modules from the database
        $allModules = DB::table('modules')->pluck('slug')->toArray();
        
        // Define core modules including the missing 'products'
        $coreModules = ['basic_crm', 'quotations', 'challans', 'billing', 'finance', 'products'];
        
        // Merge and deduplicate
        $modules = array_values(array_unique(array_merge($coreModules, $allModules)));
        $modulesJson = json_encode($modules);

        // Update Free Plan
        $freePlan = DB::table('plans')->where('slug', 'free')->first();
        
        if ($freePlan) {
            // Update Plan
            DB::table('plans')
                ->where('id', $freePlan->id)
                ->update(['module_access' => $modulesJson]);

            // Update all Subscriptions linked to the Free Plan
            DB::table('subscriptions')
                ->where('plan_id', $freePlan->id)
                ->update(['module_access' => $modulesJson]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation needed for this data fix
    }
};
