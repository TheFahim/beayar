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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_no')->nullable()->after('customer_company_id'); // Make nullable initially to avoid issues with existing data
            $table->string('attention')->nullable()->after('name');
            $table->string('designation')->nullable()->after('attention');
            $table->string('department')->nullable()->after('designation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['customer_no', 'attention', 'designation', 'department']);
        });
    }
};
