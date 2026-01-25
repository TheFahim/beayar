<?php

use App\Models\BrandOrigin;
use App\Models\Product;
use App\Models\QuotationRevision;
use App\Models\Specification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotation_products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)->constrained();
            $table->foreignIdFor(QuotationRevision::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(BrandOrigin::class)->nullable()->constrained()->nullOnDelete();
            $table->string('size')->nullable();
            $table->foreignIdFor(Specification::class)->nullable()->constrained();
            $table->string('add_spec')->nullable();
            $table->string('unit')->nullable();
            $table->string('delivery_time')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('requision_no')->nullable();
            $table->double('foreign_currency_buying')->nullable();
            $table->double('bdt_buying')->nullable();
            $table->double('weight')->nullable();
            $table->double('air_sea_freight_rate')->nullable();
            $table->double('air_sea_freight')->nullable();
            $table->double('tax_percentage')->nullable();
            $table->double('tax')->nullable();
            $table->double('att_percentage')->nullable();
            $table->double('att')->nullable();
            $table->double('margin')->nullable();
            $table->double('margin_value')->nullable();
            $table->double('unit_price')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_products');
    }
};
