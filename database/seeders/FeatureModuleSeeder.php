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
                'name' => 'Infrastructure',
                'slug' => 'infrastructure',
                'description' => 'Manage domains, hosting, VPS, VoIP and other infrastructure services',
                'icon' => 'server',
                'modules' => [
                    ['name' => 'Domains', 'slug' => 'domains'],
                    ['name' => 'Hosting', 'slug' => 'hostings'],
                    ['name' => 'VPS', 'slug' => 'vps'],
                    ['name' => 'VoIP', 'slug' => 'voip'],
                    ['name' => 'Service Providers', 'slug' => 'service-providers'],
                    ['name' => 'Domain Emails', 'slug' => 'domain-emails'],
                    ['name' => 'Other Services', 'slug' => 'other-services'],
                    ['name' => 'Renewals', 'slug' => 'expiry-trackers'],
                    ['name' => 'Assets', 'slug' => 'assets'],
                    ['name' => 'G-Mails', 'slug' => 'g-mails'],
                ],
            ],
            [
                'name' => 'Productivity',
                'slug' => 'productivity',
                'description' => 'Tasks, notes, vault and monitoring tools',
                'icon' => 'briefcase',
                'modules' => [
                    ['name' => 'Tasks', 'slug' => 'tasks'],
                    ['name' => 'Notes', 'slug' => 'notes'],
                    ['name' => 'Vault', 'slug' => 'vault'],
                    ['name' => 'Monitor', 'slug' => 'monitor'],
                    ['name' => 'Calendar', 'slug' => 'calendar'],
                ],
            ],
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
                    ['name' => 'Attachments', 'slug' => 'attachments'],
                ],
            ],
            [
                'name' => 'Integration',
                'slug' => 'integration',
                'description' => 'Webhooks, tokens, import/export and reporting',
                'icon' => 'link',
                'modules' => [
                    ['name' => 'Webhooks', 'slug' => 'webhooks'],
                    ['name' => 'API Tokens', 'slug' => 'tokens'],
                    ['name' => 'Import', 'slug' => 'import'],
                    ['name' => 'Export', 'slug' => 'export'],
                    ['name' => 'Reports', 'slug' => 'reports'],
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
                            'can_import' => true,
                        ]
                    );
                }
            }
        }
    }
}
