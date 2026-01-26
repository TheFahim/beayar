<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_company_id')->constrained('user_companies')->cascadeOnDelete();
            $table->foreignId('quotation_id')->constrained('quotations')->restrictOnDelete();
            $table->foreignId('quotation_revision_id')->nullable()->constrained('quotation_revisions')->nullOnDelete();

            // Hierarchy for Running Bills
            $table->foreignId('parent_bill_id')->nullable()->constrained('bills')->nullOnDelete();

            $table->enum('bill_type', ['advance', 'regular', 'running'])->default('regular');
            $table->string('invoice_no')->unique();
            $table->date('bill_date');
            $table->date('payment_received_date')->nullable();

            // Financials
            $table->decimal('total_amount', 15, 2)->default(0); // Gross
            $table->decimal('bill_percentage', 5, 2)->default(0); // For partial billing
            $table->decimal('bill_amount', 15, 2)->default(0); // Net bill
            $table->double('due')->default(0);
            $table->double('shipping')->default(0);
            $table->decimal('discount', 15, 2)->default(0.00);

            $table->enum('status', ['draft', 'issued', 'paid', 'cancelled'])->default('issued');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('quotation_id');
            $table->index('quotation_revision_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
