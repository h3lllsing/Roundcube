<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->index('deleted_at');
        });

        Schema::table('email_accounts', function (Blueprint $table) {
            $table->index('deleted_at');
            $table->index('status');
        });

        Schema::table('email_account_user', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('deleted_at');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('email_account_user', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('email_accounts', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });
    }
};
