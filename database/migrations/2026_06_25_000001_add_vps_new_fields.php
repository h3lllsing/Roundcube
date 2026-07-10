<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vps', function (Blueprint $table) {
            $table->string('department')->nullable()->after('cpu_cores');
            $table->string('location')->nullable()->after('department');
            $table->json('login_ids')->nullable()->after('location');
            $table->json('additional_ips')->nullable()->after('login_ids');
        });
    }

    public function down(): void
    {
        Schema::table('vps', function (Blueprint $table) {
            $table->dropColumn(['department', 'location', 'login_ids', 'additional_ips']);
        });
    }
};
