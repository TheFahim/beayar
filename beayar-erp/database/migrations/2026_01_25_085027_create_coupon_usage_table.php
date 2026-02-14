<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupon_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->bigInteger('platform_invoice_id')->nullable();
            $table->decimal('discount_applied', 10, 2);
            $table->timestamp('used_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usage');
    }
};
