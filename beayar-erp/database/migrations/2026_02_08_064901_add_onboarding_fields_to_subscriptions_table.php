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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->change();
            $table->string('plan_type')->default('free')->after('plan_id'); // free, custom
            $table->integer('company_limit')->default(1)->after('plan_type');
            $table->integer('user_limit_per_company')->default(1)->after('company_limit');
            $table->integer('quotation_limit_per_month')->default(10)->after('user_limit_per_company');
            $table->json('module_access')->nullable()->after('quotation_limit_per_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'plan_type',
                'company_limit',
                'user_limit_per_company',
                'quotation_limit_per_month',
                'module_access',
            ]);
            $table->foreignId('plan_id')->nullable(false)->change();
        });
    }
};
