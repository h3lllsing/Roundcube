<?php

namespace Database\Seeders;

use App\Models\RoleTemplate;
use Illuminate\Database\Seeder;

class RoleTemplateSeeder extends Seeder
{
    private function allFalse(array $modules): array
    {
        return array_fill_keys($modules, [
            'can_create' => false,
            'can_read' => false,
            'can_update' => false,
            'can_delete' => false,
            'can_export' => false,
            'can_reveal' => false,
            'can_import' => false,
        ]);
    }

    public function run(): void
    {
        $infrastructure = ['domains', 'hostings', 'vps', 'voip', 'service-providers', 'domain-emails', 'other-services', 'expiry-trackers', 'assets'];
        $productivity  = ['tasks', 'notes', 'vault', 'monitor', 'calendar'];
        $adminModules  = ['users', 'roles', 'privileges', 'module-permissions', 'activity-logs', 'login-audits', 'notifications', 'attachments'];
        $integration   = ['webhooks', 'tokens', 'import', 'export', 'reports'];
        $allModules    = array_merge($infrastructure, $productivity, $adminModules, $integration);

        $allTrue = fn(array $modules) => collect($modules)->mapWithKeys(fn($m) => [
            $m => ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => true, 'can_export' => true, 'can_reveal' => true, 'can_import' => true],
        ])->toArray();

        $truePerms = ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => false, 'can_export' => false, 'can_reveal' => false, 'can_import' => false];
        $infraPerms = ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => false, 'can_export' => true, 'can_reveal' => true, 'can_import' => false];
        $readOnly   = ['can_create' => false, 'can_read' => true,  'can_update' => false, 'can_delete' => false, 'can_export' => false, 'can_reveal' => false, 'can_import' => false];
        $allDeny    = ['can_create' => false, 'can_read' => false, 'can_update' => false, 'can_delete' => false, 'can_export' => false, 'can_reveal' => false, 'can_import' => false];
        $denyAll    = $allDeny;

        $adminReadOnly = ['users' => $readOnly, 'roles' => $denyAll, 'privileges' => $denyAll, 'module-permissions' => $denyAll, 'activity-logs' => $readOnly, 'login-audits' => $readOnly, 'notifications' => $readOnly, 'attachments' => $readOnly];
        $integrationAdmin = ['webhooks' => $readOnly, 'tokens' => $denyAll, 'import' => $denyAll, 'export' => $denyAll, 'reports' => $readOnly];

        $itSupportModules = ['domains', 'hostings', 'vps', 'voip', 'service-providers', 'domain-emails'];
        $itSupportPerm    = ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => false, 'can_export' => false, 'can_reveal' => true, 'can_import' => false];

        $roModules = array_merge($infrastructure, $productivity, ['users', 'activity-logs', 'notifications', 'attachments', 'webhooks', 'reports']);

        $templates = [
            [
                'name'        => 'Super Admin',
                'slug'        => 'super-admin',
                'description' => 'Full access to all modules with all permissions. Intended for the super-admin role only. Dangerous — should not be applied to normal roles.',
                'is_dangerous' => true,
                'permissions_json' => $allTrue($allModules),
            ],
            [
                'name'        => 'Admin',
                'slug'        => 'admin',
                'description' => 'Operational administrator with infrastructure and productivity access. No RBAC/System management. No delete, import, or approval permissions.',
                'is_dangerous' => false,
                'permissions_json' => collect($infrastructure)->mapWithKeys(fn($m) => [$m => $infraPerms])
                    ->union(collect($productivity)->mapWithKeys(fn($m) => [$m => $truePerms]))
                    ->union($adminReadOnly)
                    ->union($integrationAdmin)
                    ->all(),
            ],
            [
                'name'        => 'IT Support',
                'slug'        => 'it-support',
                'description' => 'Help desk / support staff. Can manage and reveal passwords on 6 operational infrastructure modules (domains, hosting, VPS, VoIP, providers, domain emails). No delete, export, or import.',
                'is_dangerous' => false,
                'permissions_json' => collect($itSupportModules)->mapWithKeys(fn($m) => [$m => $itSupportPerm])
                    ->union($this->allFalse(array_diff($allModules, $itSupportModules)))
                    ->all(),
            ],
            [
                'name'        => 'Read Only',
                'slug'        => 'read-only',
                'description' => 'Read-only access to operational modules. Cannot create, update, delete, export, reveal, or import. Suitable for auditors and compliance personnel.',
                'is_dangerous' => false,
                'permissions_json' => collect($roModules)->mapWithKeys(fn($m) => [$m => $readOnly])
                    ->union($this->allFalse(array_diff($allModules, $roModules)))
                    ->all(),
            ],
        ];

        foreach ($templates as $data) {
            RoleTemplate::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
