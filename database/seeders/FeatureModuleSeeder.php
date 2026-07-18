<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class FeatureModuleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::all()->keyBy('slug');

        $features = [
            [
                'name' => 'Administration',
                'slug' => 'administration',
                'description' => 'User management, roles, permissions and system settings',
                'icon' => 'shield',
                'modules' => [
                    ['name' => 'Users', 'slug' => 'users'],
                    ['name' => 'Roles', 'slug' => 'roles'],
                    ['name' => 'Privileges', 'slug' => 'privileges'],
                    ['name' => 'Module Permissions', 'slug' => 'module-permissions'],
                    ['name' => 'Activity Logs', 'slug' => 'activity-logs'],
                    ['name' => 'Login Audits', 'slug' => 'login-audits'],
                    ['name' => 'Notifications', 'slug' => 'notifications'],
                ],
            ],
            [
                'name' => 'Operations',
                'slug' => 'operations',
                'description' => 'Monitoring and system oversight tools',
                'icon' => 'activity',
                'modules' => [
                    ['name' => 'Dashboard', 'slug' => 'dashboard'],
                    ['name' => 'Monitor', 'slug' => 'monitor'],
                    ['name' => 'Audit', 'slug' => 'audit'],
                ],
            ],
        ];

        $superAdmin = $roles->get('super-admin');

        foreach ($features as $featData) {
            $modules = $featData['modules'];
            unset($featData['modules']);

            $feature = Feature::updateOrCreate(
                ['slug' => $featData['slug']],
                $featData
            );

            foreach ($modules as $modData) {
                $module = Module::updateOrCreate(
                    ['feature_id' => $feature->id, 'slug' => $modData['slug']],
                    ['name' => $modData['name']]
                );

                if ($superAdmin) {
                    ModuleRolePermission::updateOrCreate(
                        ['module_id' => $module->id, 'role_id' => $superAdmin->id],
                        [
                            'can_create' => true,
                            'can_read' => true,
                            'can_update' => true,
                            'can_delete' => true,
                            'can_export' => true,
                            'can_reveal' => true,
                        ]
                    );
                }
            }
        }
    }
}
