<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_company_id')->nullable()->constrained('tenant_companies')->nullOnDelete();
            $table->enum('organization_type', ['independent','holding','subsidiary'])->default('independent');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address');
            $table->string('bin_no')->nullable();
            $table->string('logo')->nullable();
            $table->string('status')->default('active');
            $table->softDeletes();
            $table->timestamps();
            $table->index('organization_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_companies');
    }
};
