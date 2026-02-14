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
            $table->foreignId('tenant_company_id')->constrained('tenant_companies')->cascadeOnDelete();
            $table->foreignId('quotation_id')->constrained()->restrictOnDelete();
            $table->foreignId('quotation_revision_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_bill_id')->nullable()->constrained('bills')->nullOnDelete();
            $table->enum('bill_type', ['advance','regular','running'])->default('regular');
            $table->string('invoice_no');
            $table->date('bill_date');
            $table->date('payment_received_date')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('bill_percentage', 5, 2)->default(0.00);
            $table->decimal('bill_amount', 15, 2)->default(0.00);
            $table->double('due')->default(0);
            $table->double('shipping')->default(0);
            $table->decimal('discount', 15, 2)->default(0.00);
            $table->enum('status', ['draft','issued','paid','cancelled'])->default('issued');
            $table->text('notes');
            $table->softDeletes();
            $table->timestamps();
            $table->unique('invoice_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
