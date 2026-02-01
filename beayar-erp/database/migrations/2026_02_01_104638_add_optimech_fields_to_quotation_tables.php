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
        // Update quotations table
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'regular_billing_locked')) {
                $table->boolean('regular_billing_locked')->default(false)->after('status_id');
            }
            // Add quotation_no as alias/replacement for reference_no if needed, or we just map it in model
            if (!Schema::hasColumn('quotations', 'quotation_no')) {
                $table->string('quotation_no')->nullable()->unique()->after('reference_no');
            }
        });

        // Update quotation_revisions table
        Schema::table('quotation_revisions', function (Blueprint $table) {
            if (!Schema::hasColumn('quotation_revisions', 'shipping')) {
                $table->decimal('shipping', 15, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('quotation_revisions', 'saved_as')) {
                $table->enum('saved_as', ['draft', 'quotation'])->default('draft')->after('status');
            }
            // Add validity if we want to match exact column name, or we use valid_until
            if (!Schema::hasColumn('quotation_revisions', 'validity')) {
                $table->date('validity')->nullable()->after('date');
            }
        });

        // Update quotation_products table
        Schema::table('quotation_products', function (Blueprint $table) {
            $table->foreignId('brand_origin_id')->nullable()->constrained('brand_origins')->nullOnDelete();
            $table->string('size')->nullable();
            $table->foreignId('specification_id')->nullable()->constrained('specifications');
            $table->string('add_spec')->nullable();
            $table->string('unit')->nullable();
            $table->string('delivery_time')->nullable();
            $table->string('requision_no')->nullable();
            
            // Financials
            $table->decimal('foreign_currency_buying', 15, 2)->nullable();
            $table->decimal('bdt_buying', 15, 2)->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('air_sea_freight_rate', 10, 2)->nullable();
            $table->decimal('air_sea_freight', 15, 2)->nullable();
            
            $table->decimal('tax_percentage', 5, 2)->nullable(); // vs tax_rate
            $table->decimal('tax', 15, 2)->nullable();
            
            $table->decimal('att_percentage', 5, 2)->nullable();
            $table->decimal('att', 15, 2)->nullable();
            
            $table->decimal('margin', 5, 2)->nullable();
            $table->decimal('margin_value', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_products', function (Blueprint $table) {
            $table->dropForeign(['brand_origin_id']);
            $table->dropForeign(['specification_id']);
            $table->dropColumn([
                'brand_origin_id', 'size', 'specification_id', 'add_spec', 'unit',
                'delivery_time', 'requision_no', 'foreign_currency_buying', 'bdt_buying',
                'weight', 'air_sea_freight_rate', 'air_sea_freight', 'tax_percentage',
                'tax', 'att_percentage', 'att', 'margin', 'margin_value'
            ]);
        });

        Schema::table('quotation_revisions', function (Blueprint $table) {
            $table->dropColumn(['shipping', 'saved_as', 'validity']);
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['regular_billing_locked', 'quotation_no']);
        });
    }
};
