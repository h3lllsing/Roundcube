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

        $superRoleId = DB::table('roles')->where('slug', 'super-admin')->value('id');
        $adminRoleId = DB::table('roles')->where('slug', 'admin')->value('id');

        if ($superRoleId) {
            DB::table('users')
                ->whereIn('id', fn($q) => $q->select('user_id')->from('user_roles')->where('role_id', $superRoleId))
                ->update(['role' => 'super-admin']);
        }

        if ($adminRoleId) {
            DB::table('users')
                ->where('role', 'user')
                ->whereIn('id', fn($q) => $q->select('user_id')->from('user_roles')->where('role_id', $adminRoleId))
                ->update(['role' => 'admin']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
