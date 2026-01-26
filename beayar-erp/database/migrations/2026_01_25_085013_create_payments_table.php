<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_company_id')->constrained('user_companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('bill_id')->nullable()->constrained('bills');
            $table->string('payment_no')->unique();
            $table->date('date');
            $table->decimal('amount', 15, 2);
            $table->string('method')->default('cash'); // cash, bank, cheque, online
            $table->text('reference')->nullable(); // Cheque No, Transaction ID
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
