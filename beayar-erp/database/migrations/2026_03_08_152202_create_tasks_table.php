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
        Schema::create('tasks', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('tenant_company_id')->constrained('tenant_companies')->onDelete('cascade');
            $blueprint->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $blueprint->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            $blueprint->string('title');
            $blueprint->text('description')->nullable();
            $blueprint->dateTime('start_date');
            $blueprint->dateTime('end_date');
            $blueprint->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
