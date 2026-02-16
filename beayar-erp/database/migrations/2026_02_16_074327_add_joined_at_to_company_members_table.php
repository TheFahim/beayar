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
        Schema::table('company_members', function (Blueprint $table) {
            $table->date('joined_at')->nullable()->after('is_active');
        });

        // Backfill joined_at with created_at for existing records
        \Illuminate\Support\Facades\DB::table('company_members')->update([
            'joined_at' => \Illuminate\Support\Facades\DB::raw('DATE(created_at)')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_members', function (Blueprint $table) {
            $table->dropColumn('joined_at');
        });
    }
};
