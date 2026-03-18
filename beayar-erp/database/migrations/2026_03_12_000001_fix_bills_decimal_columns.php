<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The bills.due and bills.shipping columns were incorrectly defined as DOUBLE,
     * which causes floating-point precision errors in monetary calculations.
     * This migration converts them to DECIMAL(15,2).
     */
    public function up(): void
    {
        // Use DB statement for MySQL column modification
        DB::statement('ALTER TABLE bills MODIFY COLUMN due DECIMAL(15, 2) DEFAULT 0');
        DB::statement('ALTER TABLE bills MODIFY COLUMN shipping DECIMAL(15, 2) DEFAULT 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE bills MODIFY COLUMN due DOUBLE DEFAULT 0');
        DB::statement('ALTER TABLE bills MODIFY COLUMN shipping DOUBLE DEFAULT 0');
    }
};
