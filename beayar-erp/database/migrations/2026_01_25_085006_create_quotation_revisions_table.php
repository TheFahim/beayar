<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->integer('revision_number')->default(1);
            $table->json('data'); // Snapshot of the quotation data at this revision
            $table->foreignId('created_by')->constrained('users');
            $table->text('change_log')->nullable(); // Description of changes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_revisions');
    }
};
