<?php

use App\Models\Quotation;
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
        Schema::create('quotation_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Quotation::class)->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('type', ['normal', 'via'])->default('normal');
            $table->string('revision_no');
            $table->date('validity')->default(now());
            $table->string('currency')->default('BDT');
            $table->string('exchange_rate')->default(1);
            $table->double('subtotal');
            $table->double('discount_percentage')->default(0)->nullable();
            $table->double('discount_amount')->default(0)->nullable();
            $table->double('shipping')->default(0)->nullable();
            $table->double('vat_percentage')->default(0);
            $table->double('vat_amount')->default(0);
            $table->double('total');
            $table->text('terms_conditions')->nullable();
            $table->enum('saved_as', ['draft', 'quotation'])->default('draft');
            $table->boolean('is_active')->default(true);
            // created_by
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_revisions');
    }
};
