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
        Schema::create('feedback_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_company_id')->constrained('tenant_companies')->cascadeOnDelete();
            $table->foreignId('feedback_id')->constrained('feedback')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('original_name');
            $table->string('file_name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->bigInteger('size')->nullable();
            $table->timestamps();

            $table->index(['tenant_company_id', 'feedback_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_images');
    }
};
