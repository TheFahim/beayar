<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_revision_id')->constrained('quotation_revisions')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products'); // Can be custom item
            $table->foreignId('brand_origin_id')->nullable()->constrained('brand_origins')->nullOnDelete();
            $table->string('reference_no')->nullable(); // Optional product code
            $table->string('product_name'); // Store name in case product changes
            $table->text('description')->nullable();
            $table->string('size')->nullable();
            $table->foreignId('specification_id')->nullable()->constrained('specifications');
            $table->string('add_spec')->nullable();
            $table->string('unit')->nullable();
            $table->string('delivery_time')->nullable();
            $table->string('requision_no')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('foreign_currency_buying', 15, 2)->nullable();
            $table->decimal('bdt_buying', 15, 2)->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('air_sea_freight_rate', 10, 2)->nullable();
            $table->decimal('air_sea_freight', 15, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->nullable();
            $table->decimal('tax', 15, 2)->nullable();
            $table->decimal('att_percentage', 5, 2)->nullable();
            $table->decimal('att', 15, 2)->nullable();
            $table->decimal('margin', 5, 2)->nullable();
            $table->decimal('margin_value', 15, 2)->nullable();
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_products');
    }
};
