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
        Schema::table('bills', function (Blueprint $table) {
            // Track the chain of reissued bills
            $table->foreignId('reissued_from_id')->nullable()->after('parent_bill_id')
                ->constrained('bills')->nullOnDelete();
            $table->foreignId('reissued_to_id')->nullable()->after('reissued_from_id')
                ->constrained('bills')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['reissued_from_id']);
            $table->dropForeign(['reissued_to_id']);
            $table->dropColumn(['reissued_from_id', 'reissued_to_id']);
        });
    }
};
