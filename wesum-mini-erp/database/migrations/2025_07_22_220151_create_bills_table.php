<?php

use App\Models\Bill;
use App\Models\Challan;
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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Challan::class)->constrained();
            $table->string('bill_no')->unique();
            $table->double('payable');
            $table->double('paid');
            $table->double('due');
            $table->double('vat');
            $table->double('att');
            $table->double('delivery_cost');
            $table->double('buying_price');
            $table->double('profit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
