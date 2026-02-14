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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->bigInteger('current_tenant_company_id')->nullable();
            $table->string('current_scope')->default('personal');
            $table->softDeletes();
            $table->timestamps();
            $table->unique('email');
            $table->index('current_tenant_company_id');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email');
            $table->string('token');
            $table->timestamps();
            $table->primary('email');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id');
            $table->bigInteger('user_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent');
            $table->longText('payload');
            $table->integer('last_activity');
            $table->primary('id');
            $table->index('user_id');
            $table->index('last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
