<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->string('revision_no')->default('R0');
            $table->date('date');
            $table->date('validity')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('currency')->default('BDT');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->enum('type', ['normal','via'])->default('normal');
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('shipping', 15, 2)->default(0.00);
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('shipping_cost', 15, 2)->default(0.00);
            $table->decimal('vat_percentage', 5, 2)->default(0.00);
            $table->decimal('vat_amount', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00);
            $table->text('terms_conditions');
            $table->enum('status', ['draft','sent','approved','rejected'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->enum('saved_as', ['draft','quotation'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_revisions');
    }
};
