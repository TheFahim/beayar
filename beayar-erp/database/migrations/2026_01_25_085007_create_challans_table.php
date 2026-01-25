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
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations');
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('challan_no')->unique();
            $table->date('date');
            $table->text('delivery_address')->nullable();
            $table->string('status')->default('pending'); // pending, delivered
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challans');
    }
};
