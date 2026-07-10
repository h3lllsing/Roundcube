<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vps', function (Blueprint $table) {
            $table->text('password')->nullable()->after('ip_address');
        });

        Schema::table('service_providers', function (Blueprint $table) {
            $table->text('password')->nullable()->after('website');
        });
    }

    public function down(): void
    {
        Schema::table('vps', function (Blueprint $table) {
            $table->dropColumn('password');
        });

        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};
