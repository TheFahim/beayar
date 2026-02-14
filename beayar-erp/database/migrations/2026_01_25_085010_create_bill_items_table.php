<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_challan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quotation_product_id')->constrained();
            $table->foreignId('challan_product_id')->constrained();
            $table->integer('quantity')->default(0);
            $table->integer('remaining_quantity')->default(0);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('bill_price', 15, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_items');
    }
};
