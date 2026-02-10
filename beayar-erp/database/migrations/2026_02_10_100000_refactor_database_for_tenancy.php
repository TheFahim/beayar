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
        // 1. Create Tenants Table
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Account Owner
            $table->string('name')->nullable(); // Optional, e.g. "Acme Corp Billing"
            $table->timestamps();
        });

        // 2. Modify User Companies (Companies) Table
        Schema::table('user_companies', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
        });

        // 3. Modify Subscriptions Table
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            // Make user_id nullable as subscription is now primarily linked to tenant
            $table->foreignId('user_id')->nullable()->change();
        });

        // 4. Update Company Members Pivot
        Schema::table('company_members', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_members', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
            // Revert user_id to not null (caution: this might fail if data was changed)
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('user_companies', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::dropIfExists('tenants');
    }
};
