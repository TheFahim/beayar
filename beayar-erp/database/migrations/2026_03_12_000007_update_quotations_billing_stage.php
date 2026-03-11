<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Replaces the boolean regular_billing_locked with a more expressive billing_stage ENUM.
     *
     * Stages:
     * - none: No billing activity yet
     * - advance_pending: Advance bill created but not issued
     * - advance_issued: Advance bill issued
     * - running_in_progress: Running bills being created
     * - regular_pending: Regular bill created but not issued
     * - completed: Regular bill issued (billing complete)
     * - cancelled: All bills cancelled
     */
    public function up(): void
    {
        // First, add the new column
        Schema::table('quotations', function (Blueprint $table) {
            $table->enum('billing_stage', [
                'none',
                'advance_pending',
                'advance_issued',
                'running_in_progress',
                'regular_pending',
                'completed',
                'cancelled'
            ])->default('none')->after('regular_billing_locked');
        });

        // Migrate data from regular_billing_locked to billing_stage
        DB::statement("
            UPDATE quotations
            SET billing_stage = CASE
                WHEN regular_billing_locked = 1 THEN 'regular_pending'
                ELSE 'none'
            END
        ");

        // Add index
        Schema::table('quotations', function (Blueprint $table) {
            $table->index(['billing_stage', 'tenant_company_id'], 'quotations_stage_tenant_index');
        });

        // Now drop the old column
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('regular_billing_locked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the old column
        Schema::table('quotations', function (Blueprint $table) {
            $table->boolean('regular_billing_locked')->default(false)->after('customer_id');
        });

        // Migrate data back
        DB::statement("
            UPDATE quotations
            SET regular_billing_locked = CASE
                WHEN billing_stage = 'regular_pending' THEN 1
                ELSE 0
            END
        ");

        // Drop the new column
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropIndex('quotations_stage_tenant_index');
            $table->dropColumn('billing_stage');
        });
    }
};
