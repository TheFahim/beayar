<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('role')->default('super_admin');
            $table->rememberToken();
            $table->timestamps();
            $table->unique('email');
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('value');
            $table->string('group')->default('general');
            $table->timestamps();
            $table->unique('key');
        });

        Schema::create('platform_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('subscription_id')->nullable()->constrained();
            $table->string('invoice_number');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0.00);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            $table->string('status')->default('pending');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique('invoice_number');
        });

        Schema::create('platform_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_invoice_id')->constrained();
            $table->string('transaction_id');
            $table->string('provider');
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('USD');
            $table->string('status');
            $table->json('payment_method_details')->nullable();
            $table->timestamps();
            $table->unique('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_payments');
        Schema::dropIfExists('platform_invoices');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('admins');
    }
};
