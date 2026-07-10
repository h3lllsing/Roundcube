<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::all()->keyBy('slug');
        $modules = Module::all();

        $rolePermissions = [
            'admin' => [
                'can_create' => true, 'can_read' => true, 'can_update' => true,
                'can_delete' => true, 'can_export' => true,
                'can_reveal' => false,
            ],
            'customer' => [
                'can_create' => true, 'can_read' => true, 'can_update' => true,
                'can_delete' => false, 'can_export' => true,
                'can_reveal' => false,
            ],
            'editor' => [
                'can_create' => true, 'can_read' => true, 'can_update' => true,
                'can_delete' => false, 'can_export' => false,
                'can_reveal' => false,
            ],
            'user' => [
                'can_create' => true, 'can_read' => true, 'can_update' => false,
                'can_delete' => false, 'can_export' => false,
                'can_reveal' => false,
            ],
        ];

        foreach ($modules as $module) {
            foreach ($rolePermissions as $slug => $perms) {
                $role = $roles->get($slug);
                if ($role) {
                    ModuleRolePermission::updateOrCreate(
                        ['module_id' => $module->id, 'role_id' => $role->id],
                        $perms
                    );
                }
            }
        }

        // Assign user role to test@example.com, replace any existing roles
        $testUser = User::where('email', 'test@example.com')->first();
        $userRole = Role::where('slug', 'user')->first();
        if ($testUser && $userRole) {
            $testUser->roles()->sync([$userRole->id]);
        }
    }
}
