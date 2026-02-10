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
            $table->foreignId('bill_challan_id')->constrained('bill_challans')->cascadeOnDelete();
            $table->foreignId('quotation_product_id')->constrained('quotation_products');
            $table->foreignId('challan_product_id')->constrained('challan_products');

            $table->integer('quantity')->default(0);
            $table->integer('remaining_quantity')->default(0);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('bill_price', 15, 2)->default(0.00); // Calculated total for this item

            $table->timestamps();

            $table->index('bill_challan_id');
            $table->index('quotation_product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_items');
    }
};
