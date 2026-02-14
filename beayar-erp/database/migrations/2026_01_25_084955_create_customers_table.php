<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_company_id')->constrained('tenant_companies')->cascadeOnDelete();
            $table->foreignId('customer_company_id')->constrained('customer_companies')->cascadeOnDelete();
            $table->string('customer_no')->nullable();
            $table->string('name');
            $table->string('attention')->nullable();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address');
            $table->string('type')->default('individual');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
