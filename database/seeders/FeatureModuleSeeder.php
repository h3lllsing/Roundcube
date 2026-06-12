<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Database\Seeder;

class FeatureModuleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::all()->keyBy('slug');

        $features = [
            [
                'name' => 'Account Management',
                'slug' => 'account-management',
                'description' => 'Manage user accounts and profiles',
                'icon' => 'users',
                'modules' => [
                    ['name' => 'User Accounts', 'slug' => 'user-accounts'],
                    ['name' => 'Account Types', 'slug' => 'account-types'],
                    ['name' => 'Profile Settings', 'slug' => 'profile-settings'],
                ],
            ],
            [
                'name' => 'Services',
                'slug' => 'services',
                'description' => 'Manage service requests and delivery',
                'icon' => 'settings',
                'modules' => [
                    ['name' => 'Service Requests', 'slug' => 'service-requests'],
                    ['name' => 'Service Categories', 'slug' => 'service-categories'],
                    ['name' => 'Service Providers', 'slug' => 'service-providers'],
                ],
            ],
            [
                'name' => 'Reports',
                'slug' => 'reports',
                'description' => 'Generate and manage reports',
                'icon' => 'bar-chart',
                'modules' => [
                    ['name' => 'Standard Reports', 'slug' => 'standard-reports'],
                    ['name' => 'Custom Reports', 'slug' => 'custom-reports'],
                    ['name' => 'Export History', 'slug' => 'export-history'],
                ],
            ],
            [
                'name' => 'Settings',
                'slug' => 'settings',
                'description' => 'System configuration and preferences',
                'icon' => 'settings',
                'modules' => [
                    ['name' => 'General Settings', 'slug' => 'general-settings'],
                    ['name' => 'Security Settings', 'slug' => 'security-settings'],
                    ['name' => 'Email Templates', 'slug' => 'email-templates'],
                ],
            ],
        ];

        $superAdmin = $roles->get('super-admin');

        foreach ($features as $featData) {
            $modules = $featData['modules'];
            unset($featData['modules']);

            $feature = Feature::create($featData);

            foreach ($modules as $modData) {
                $module = Module::create([
                    'feature_id' => $feature->id,
                    'name' => $modData['name'],
                    'slug' => $modData['slug'],
                ]);

                if ($superAdmin) {
                    ModuleRolePermission::create([
                        'module_id' => $module->id,
                        'role_id' => $superAdmin->id,
                        'can_create' => true,
                        'can_read' => true,
                        'can_update' => true,
                        'can_delete' => true,
                        'can_approve' => true,
                        'can_export' => true,
                    ]);
                }
            }
        }
    }
}
