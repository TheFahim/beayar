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
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('super_admin'); // super_admin, support
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general'); // payment, email, site
            $table->timestamps();
        });

        Schema::create('platform_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Tenant User
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions');
            $table->string('invoice_number')->unique(); // INV-PLAT-2024-001
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('status')->default('pending'); // pending, paid, void, failed
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('platform_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_invoice_id')->constrained('platform_invoices');
            $table->string('transaction_id')->unique(); // Stripe/Paddle ID
            $table->string('provider'); // stripe, paddle
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('USD');
            $table->string('status'); // succeeded, failed, pending
            $table->json('payment_method_details')->nullable();
            $table->timestamps();
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
