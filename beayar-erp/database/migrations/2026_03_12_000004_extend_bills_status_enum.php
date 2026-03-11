<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Extends the status ENUM to include:
     * - partially_paid: Bill has partial payments recorded
     * - adjusted: Bill has been adjusted (e.g., credit applied, cancelled with adjustment)
     */
    public function up(): void
    {
        // MySQL requires re-specifying the entire ENUM
        DB::statement("
            ALTER TABLE bills
            MODIFY COLUMN status ENUM('draft', 'issued', 'paid', 'cancelled', 'partially_paid', 'adjusted')
            DEFAULT 'draft'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, update any rows with new statuses to closest old status
        DB::statement("UPDATE bills SET status = 'paid' WHERE status = 'partially_paid'");
        DB::statement("UPDATE bills SET status = 'paid' WHERE status = 'adjusted'");

        // Revert to original ENUM
        DB::statement("
            ALTER TABLE bills
            MODIFY COLUMN status ENUM('draft', 'issued', 'paid', 'cancelled')
            DEFAULT 'draft'
        ");
    }
};
