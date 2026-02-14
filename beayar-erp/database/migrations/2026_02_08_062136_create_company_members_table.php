<?php

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
        Schema::create('company_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_company_id')->constrained('tenant_companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('employee');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_company_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_members');
    }
};
