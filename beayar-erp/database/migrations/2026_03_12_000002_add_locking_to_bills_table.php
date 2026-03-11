<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds locking mechanism columns to support the 6-rule bill locking system.
     * - is_locked: Boolean flag for quick checks
     * - lock_reason: ENUM explaining why the bill is locked
     * - locked_at: Timestamp when the bill was locked
     */
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('status');
            $table->enum('lock_reason', [
                'status_not_draft',
                'has_issued_child',
                'has_payments',
                'challan_quantity_violation',
                'advance_applied',
                'has_advance_adjustments',
            ])->nullable()->after('is_locked');
            $table->timestamp('locked_at')->nullable()->after('lock_reason');
        });

        // Add index for locked status queries
        Schema::table('bills', function (Blueprint $table) {
            $table->index(['is_locked', 'tenant_company_id'], 'bills_locked_tenant_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex('bills_locked_tenant_index');
            $table->dropColumn(['is_locked', 'lock_reason', 'locked_at']);
        });
    }
};
