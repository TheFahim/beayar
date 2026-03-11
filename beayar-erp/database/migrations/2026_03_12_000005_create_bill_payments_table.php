<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the bill_payments table for comprehensive payment tracking.
     * Each payment record tracks:
     * - Amount paid
     * - Payment method
     * - Payment date
     * - Reference number (check, transaction ID, etc.)
     * - Notes
     */
    public function up(): void
    {
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->foreignId('tenant_company_id')->constrained('tenant_companies')->cascadeOnDelete();

            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'check',
                'credit_card',
                'mfs',
                'other'
            ]);
            $table->date('payment_date');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['bill_id', 'tenant_company_id'], 'bill_payments_bill_tenant_index');
            $table->index(['payment_date', 'tenant_company_id'], 'bill_payments_date_tenant_index');
            $table->index('tenant_company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_payments');
    }
};
