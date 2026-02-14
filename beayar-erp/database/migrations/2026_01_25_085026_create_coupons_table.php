<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_company_id')->nullable()->constrained('tenant_companies')->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('coupon_campaigns')->cascadeOnDelete();
            $table->string('code');
            $table->string('type');
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->decimal('max_discount_amount', 15, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
