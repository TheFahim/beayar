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
        Schema::create('bill_challans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Bill::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Challan::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Indexes for performance
            $table->index('bill_id');
            $table->index('challan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_challans', function (Blueprint $table) {
            $table->dropIndex(['bill_id']);
            $table->dropIndex(['challan_id']);
        });
        Schema::dropIfExists('bill_challans');
    }
};
