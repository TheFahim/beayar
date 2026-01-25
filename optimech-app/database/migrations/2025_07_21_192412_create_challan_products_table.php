<?php

use App\Models\Challan;
use App\Models\QuotationProduct;
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
        Schema::create('challan_products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(QuotationProduct::class)->constrained();
            $table->foreignIdFor(Challan::class)->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challan_products');
    }
};
