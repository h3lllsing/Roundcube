<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $superAdminRoleId = Role::where('slug', 'super-admin')->value('id');

        if ($superAdminRoleId) {
            DB::table('module_role_permissions')
                ->where('role_id', '!=', $superAdminRoleId)
                ->where('can_delete', true)
                ->update(['can_delete' => false]);

            $superAdminUserIds = DB::table('user_roles')
                ->where('role_id', $superAdminRoleId)
                ->pluck('user_id');

            if ($superAdminUserIds->isNotEmpty()) {
                DB::table('user_module_permissions')
                    ->whereNotIn('user_id', $superAdminUserIds)
                    ->where('can_delete', true)
                    ->update(['can_delete' => false]);
            }
        }
    }

    public function down(): void
    {
    }
};