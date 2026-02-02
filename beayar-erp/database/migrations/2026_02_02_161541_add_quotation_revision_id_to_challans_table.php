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
        Schema::table('challans', function (Blueprint $table) {
            $table->foreignId('quotation_revision_id')->nullable()->after('quotation_id')->constrained('quotation_revisions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challans', function (Blueprint $table) {
            $table->dropForeign(['quotation_revision_id']);
            $table->dropColumn('quotation_revision_id');
        });
    }
};
