<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds credit tracking columns:
     * - advance_applied_amount: Total advance credit applied to this bill
     * - net_payable_amount: Final amount after credit application
     */
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->decimal('advance_applied_amount', 15, 2)->default(0)->after('total_amount');
            $table->decimal('net_payable_amount', 15, 2)->nullable()->after('advance_applied_amount');
        });

        // Update existing bills: net_payable_amount = total_amount (no advance applied yet)
        DB::statement('UPDATE bills SET net_payable_amount = total_amount WHERE net_payable_amount IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['advance_applied_amount', 'net_payable_amount']);
        });
    }
};
