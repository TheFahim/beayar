<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_company_id')->constrained('tenant_companies')->cascadeOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('quotation_revision_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('challan_no');
            $table->date('date');
            $table->date('delivery_date')->nullable();
            $table->text('delivery_address');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challans');
    }
};
