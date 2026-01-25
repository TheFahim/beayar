<?php

use App\Models\Quotation;
use App\Models\QuotationRevision;
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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Quotation::class)
                ->constrained()
                ->restrictOnDelete();
            $table->foreignIdFor(QuotationRevision::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->unsignedBigInteger('parent_bill_id')->nullable();
            $table->enum('bill_type', ['advance', 'regular', 'running'])->default('regular');
            $table->string('invoice_no')->unique();
            $table->date('bill_date');
            $table->date('payment_received_date')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('bill_percentage', 5, 2)->default(0);
            $table->decimal('bill_amount', 15, 2)->default(0);
            $table->double('due')->default(0);
            $table->double('shipping')->default(0);
            $table->decimal('discount', 15, 2)->default(0.00);
            $table->enum('status', ['draft', 'issued', 'paid', 'cancelled'])->default('issued');
            $table->text('notes');
            $table->timestamps();

            // Indexes for performance
            $table->index('quotation_id');
            $table->index('quotation_revision_id');

            // Foreign key for parent bill
            $table->foreign('parent_bill_id')->references('id')->on('bills')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['parent_bill_id']);
            $table->dropIndex(['quotation_id']);
            $table->dropIndex(['quotation_revision_id']);
            $table->dropColumn(['parent_bill_id', 'discount']);
        });
        Schema::dropIfExists('bills');
    }
};
