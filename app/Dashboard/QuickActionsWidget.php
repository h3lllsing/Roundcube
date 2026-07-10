<?php

namespace App\Dashboard;

use App\Models\Module;
use App\Models\User;

class QuickActionsWidget
{
    public const SLUG = 'quick-actions';

    public function cacheTtl(): int
    {
        return 300;
    }

    public function data(User $user, ?array $accessibleIds = null): array
    {
        $isSA = $user->hasRole('super-admin');

        $modules = Module::whereIn('slug', ['domains', 'hostings', 'vps', 'voip', 'vault', 'assets'])
            ->get()
            ->keyBy('slug');

        $canCreateDomain = $isSA || ($modules->get('domains') && $user->canOnModule($modules->get('domains'), 'create'));
        $canCreateHosting = $isSA || ($modules->get('hostings') && $user->canOnModule($modules->get('hostings'), 'create'));
        $canCreateVps = $isSA || ($modules->get('vps') && $user->canOnModule($modules->get('vps'), 'create'));
        $canCreateVoip = $isSA || ($modules->get('voip') && $user->canOnModule($modules->get('voip'), 'create'));
        $canCreateVault = $isSA || ($modules->get('vault') && $user->canOnModule($modules->get('vault'), 'create'));
        $canCreateAsset = $isSA || ($modules->get('assets') && $user->canOnModule($modules->get('assets'), 'create'));

        return [
            'quick_actions' => [
                'can_manage_system' => $isSA,
                'can_create_domain' => $canCreateDomain,
                'can_create_hosting' => $canCreateHosting,
                'can_create_vps' => $canCreateVps,
                'can_create_voip' => $canCreateVoip,
                'can_create_vault' => $canCreateVault,
                'can_create_asset' => $canCreateAsset,
                'can_create_task' => true,
            ],
        ];
    }
}
