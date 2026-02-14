<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description');
            $table->decimal('base_price', 10, 2);
            $table->string('billing_cycle')->default('monthly');
            $table->json('limits')->nullable();
            $table->json('module_access')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
