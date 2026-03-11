<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds performance indexes for common billing queries:
     * - Bills by quotation
     * - Bills by status and tenant
     * - Bills by type and tenant
     * - Bills by parent (for running bills)
     */
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            // Index for fetching all bills for a quotation
            $table->index(['quotation_id', 'tenant_company_id'], 'bills_quotation_tenant_index');

            // Index for filtering by status
            $table->index(['status', 'tenant_company_id'], 'bills_status_tenant_index');

            // Index for filtering by bill type
            $table->index(['bill_type', 'tenant_company_id'], 'bills_type_tenant_index');

            // Index for parent bill queries (running bills)
            $table->index(['parent_bill_id', 'tenant_company_id'], 'bills_parent_tenant_index');

            // Composite index for common query: tenant + status + bill_type
            $table->index(['tenant_company_id', 'status', 'bill_type'], 'bills_tenant_status_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex('bills_quotation_tenant_index');
            $table->dropIndex('bills_status_tenant_index');
            $table->dropIndex('bills_type_tenant_index');
            $table->dropIndex('bills_parent_tenant_index');
            $table->dropIndex('bills_tenant_status_type_index');
        });
    }
};
