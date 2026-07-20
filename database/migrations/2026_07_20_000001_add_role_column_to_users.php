<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('email');
        });

        DB::statement('UPDATE users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id SET u.role = "super-admin" WHERE r.slug = "super-admin"');
        DB::statement('UPDATE users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id SET u.role = "admin" WHERE u.role = "user" AND r.slug = "admin"');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
