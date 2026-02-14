<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_company_id')->constrained('tenant_companies')->cascadeOnDelete();
            $table->string('company_code')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address');
            $table->string('bin_no')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_companies');
    }
};
