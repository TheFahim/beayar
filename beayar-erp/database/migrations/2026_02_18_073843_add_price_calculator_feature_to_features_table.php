<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert the new feature
        DB::table('features')->insertOrIgnore([
            'name' => 'Advanced Price Calculator',
            'slug' => 'module_price_calculator',
            'description' => 'Calculate prices using foreign currency, tax, vat, margin, etc.',
            'is_active' => true,
            'sort_order' => 15, // Adjust as needed
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the feature
        DB::table('features')->where('slug', 'module_price_calculator')->delete();
    }
};
