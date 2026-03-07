<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the quotation_statuses table first (if it exists)
        Schema::dropIfExists('quotation_statuses');
        
        // Drop the status_id column and foreign key from quotations table (if they exist)
        if (Schema::hasColumn('quotations', 'status_id')) {
            Schema::table('quotations', function ($table) {
                // Only try to drop foreign key if it exists
                try {
                    $table->dropForeign(['status_id']);
                } catch (\Exception $e) {
                    // Foreign key doesn't exist, continue
                }
                $table->dropColumn('status_id');
            });
        }
    }

    public function down(): void
    {
        // Recreate quotation_statuses table
        Schema::create('quotation_statuses', function ($table) {
            $table->id();
            $table->unsignedBigInteger('tenant_company_id')->nullable();
            $table->string('name');
            $table->string('color')->default('gray');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('tenant_company_id', 'quotation_statuses_tenant_company_id_foreign')
                ->references('id')
                ->on('tenant_companies')
                ->onDelete('cascade');
        });

        // Recreate status_id column on quotations table
        Schema::table('quotations', function ($table) {
            $table->unsignedBigInteger('status_id')->after('user_id')->nullable();
            $table->foreign('status_id', 'quotations_status_id_foreign')
                ->references('id')
                ->on('quotation_statuses');
        });
    }
};
