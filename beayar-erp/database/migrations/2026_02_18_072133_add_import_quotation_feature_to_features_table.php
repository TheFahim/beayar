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
            'name' => 'Import Quotation',
            'slug' => 'module_import_quotation',
            'description' => 'Create quotations via import (foreign currency pricing).',
            'is_active' => true,
            'sort_order' => 10, // Adjust as needed
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
        DB::table('features')->where('slug', 'module_import_quotation')->delete();
    }
};
