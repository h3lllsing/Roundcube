<?php

namespace App\Http\View\Composers;

use App\Models\Module;
use App\Models\VaultEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SidebarComposer
{
    private array $moduleSlugMap = [
        'showProviders' => 'service-providers',
        'showHostings' => 'hostings',
        'showDomains' => 'domains',
        'showEmails' => 'domain-emails',
        'showVoip' => 'voip',
        'showVps' => 'vps',
        'showOtherServices' => 'other-services',
        'showExpiryTrackers' => 'expiry-trackers',
        'showAssets' => 'assets',
        'showGMails' => 'g-mails',
        'showVault' => 'vault',
        'showNotes' => 'notes',
    ];

    public function compose(View $view): void
    {
        $user = Auth::user();
        $data = [];

        if (!$user) {
            foreach ($this->moduleSlugMap as $key => $slug) {
                $data[$key] = false;
            }
            $data['showMyVault'] = false;
            $data['showMonitoring'] = false;
            $view->with($data);
            return;
        }

        if ($user->hasRole('super-admin')) {
            foreach ($this->moduleSlugMap as $key => $slug) {
                $data[$key] = true;
            }
            $data['showMyVault'] = true;
            $data['showMonitoring'] = true;
            $view->with($data);
            return;
        }

        $accessibleIds = $user->getAccessibleModuleIds('read');

        $modulesBySlug = \App\Helpers\ModuleCache::allBySlug();

        foreach ($this->moduleSlugMap as $key => $slug) {
            $module = $modulesBySlug[$slug] ?? null;
            $data[$key] = $module && in_array($module->id, $accessibleIds);
        }

        $vaultModule = $modulesBySlug['vault'] ?? null;
        $hasVaultRead = $vaultModule && in_array($vaultModule->id, $accessibleIds);
        $ownsVaultEntries = VaultEntry::where('user_id', $user->id)->exists();
        $data['showMyVault'] = $hasVaultRead || $ownsVaultEntries;

        $monitoredSlugs = ['hostings', 'vps', 'voip', 'domains', 'domain-emails', 'other-services', 'service-providers', 'expiry-trackers'];
        $hasAnyMonitoredModule = false;
        foreach ($monitoredSlugs as $slug) {
            $module = $modulesBySlug[$slug] ?? null;
            if ($module && in_array($module->id, $accessibleIds)) {
                $hasAnyMonitoredModule = true;
                break;
            }
        }
        $data['showMonitoring'] = $hasAnyMonitoredModule;

        $view->with($data);
    }
}
