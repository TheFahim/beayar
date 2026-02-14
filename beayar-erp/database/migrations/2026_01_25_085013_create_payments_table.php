<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_company_id')->constrained('tenant_companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('bill_id')->nullable()->constrained();
            $table->string('payment_no');
            $table->date('date');
            $table->decimal('amount', 15, 2);
            $table->string('method')->default('cash');
            $table->text('reference');
            $table->text('notes');
            $table->softDeletes();
            $table->timestamps();
            $table->unique('payment_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
