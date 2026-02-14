<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challan_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quotation_product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->text('remarks');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challan_products');
    }
};
