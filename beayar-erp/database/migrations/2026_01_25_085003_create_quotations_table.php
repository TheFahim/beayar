<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_company_id')->constrained('tenant_companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('status_id')->constrained('quotation_statuses');
            $table->boolean('regular_billing_locked')->default(false);
            $table->string('reference_no');
            $table->string('quotation_no')->nullable();
            $table->string('po_no')->nullable();
            $table->date('po_date')->nullable();
            $table->text('ship_to');
            $table->softDeletes();
            $table->timestamps();
            $table->unique('reference_no');
            $table->unique('quotation_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
