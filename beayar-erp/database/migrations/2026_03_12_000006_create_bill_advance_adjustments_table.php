<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the bill_advance_adjustments table for tracking advance credit applications.
     *
     * This table links:
     * - advance_bill_id: The advance bill providing the credit
     * - final_bill_id: The regular bill receiving the credit
     * - amount: How much credit was applied
     *
     * This enables:
     * - Tracking unapplied advance balance
     * - Reversing credit on bill cancellation
     * - Audit trail of all credit movements
     */
    public function up(): void
    {
        Schema::create('bill_advance_adjustments', function (Blueprint $table) {
            $table->id();

            // The advance bill providing the credit
            $table->foreignId('advance_bill_id')->constrained('bills')->cascadeOnDelete();

            // The final/regular bill receiving the credit
            $table->foreignId('final_bill_id')->constrained('bills')->cascadeOnDelete();

            $table->foreignId('tenant_company_id')->constrained('tenant_companies')->cascadeOnDelete();

            // Amount of advance credit applied
            $table->decimal('amount', 15, 2);

            // Audit fields
            $table->foreignId('created_by')->constrained('users');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['advance_bill_id', 'tenant_company_id'], 'baa_advance_tenant_index');
            $table->index(['final_bill_id', 'tenant_company_id'], 'baa_final_tenant_index');
            $table->index('tenant_company_id');

            // Composite unique index to prevent duplicate adjustments
            $table->unique(['advance_bill_id', 'final_bill_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_advance_adjustments');
    }
};
