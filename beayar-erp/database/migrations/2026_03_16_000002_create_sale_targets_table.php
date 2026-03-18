<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the sale_targets table for tracking monthly sales targets.
     */
    public function up(): void
    {
        Schema::create('sale_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_company_id')->constrained('tenant_companies')->cascadeOnDelete();

            // Target for the month in Y-m format
            $table->string('month', 7)->index();

            // Target and achieved amounts
            $table->decimal('target_amount', 15, 2)->default(0);
            $table->decimal('achieved_amount', 15, 2)->default(0);

            // Optional date range
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();

            // Unique constraint per tenant per month
            $table->unique(['tenant_company_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_targets');
    }
};
