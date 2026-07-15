<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Help Center Category Registry
    |--------------------------------------------------------------------------
    |
    | Categories with audience = 'super-admin' are hidden from and denied to
    | non-Super Admin users. All authorization continues to use the
    | application's existing RBAC system (Tyro roles).
    |
    */
    'categories' => [
        'getting-started' => [
            'label' => 'Getting Started',
            'weight' => 0,
            'audience' => 'all',
        ],

        'portal-reference-features' => [
            'label' => 'Features',
            'weight' => 100,
            'audience' => 'all',
        ],

        'portal-reference-infrastructure' => [
            'label' => 'Infrastructure',
            'weight' => 110,
            'audience' => 'all',
        ],

        'portal-reference-credentials' => [
            'label' => 'Credentials & Vault',
            'weight' => 120,
            'audience' => 'all',
        ],

        'portal-reference-operations' => [
            'label' => 'Operations',
            'weight' => 130,
            'audience' => 'all',
        ],

        'administration' => [
            'label' => 'Administration',
            'weight' => 200,
            'audience' => 'super-admin',
        ],

        'troubleshooting' => [
            'label' => 'Troubleshooting & FAQ',
            'weight' => 300,
            'audience' => 'all',
        ],

        'reference' => [
            'label' => 'Reference',
            'weight' => 400,
            'audience' => 'all',
        ],

        'version-history' => [
            'label' => 'Version History',
            'weight' => 500,
            'audience' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Registry
    |--------------------------------------------------------------------------
    |
    | Each entry defines a Help Center document. Only documents registered
    | here are resolvable through the Help Center. The audience field
    | determines visibility; authorization uses the app's RBAC system.
    |
    | The 'file' path is relative to the project root (base_path()).
    |
    */
    'documents' => [

        // ── Getting Started ───────────────────────────────────────────
        'quick-start' => [
            'title' => 'Quick Start Guide',
            'file' => 'help/getting-started/quick-start.md',
            'category' => 'getting-started',
            'weight' => 0,
            'audience' => 'all',
            'searchable' => true,
        ],

        'understanding-permissions' => [
            'title' => 'Understanding Permissions',
            'file' => 'help/getting-started/understanding-permissions.md',
            'category' => 'getting-started',
            'weight' => 10,
            'audience' => 'all',
            'searchable' => true,
        ],

        'my-permissions' => [
            'title' => 'My Permissions',
            'file' => 'help/getting-started/my-permissions.md',
            'category' => 'getting-started',
            'weight' => 20,
            'audience' => 'all',
            'searchable' => true,
        ],

        // ── Features ──────────────────────────────────────────────────
        'dashboard' => [
            'title' => 'Dashboard',
            'file' => 'help/portal-reference/features/dashboard.md',
            'category' => 'portal-reference-features',
            'weight' => 0,
            'audience' => 'all',
            'searchable' => true,
        ],

        // ── Infrastructure ────────────────────────────────────────────
        'domains' => [
            'title' => 'Domains',
            'file' => 'help/portal-reference/features/domains.md',
            'category' => 'portal-reference-infrastructure',
            'weight' => 0,
            'audience' => 'all',
            'searchable' => true,
        ],

        'hostings' => [
            'title' => 'Hostings',
            'file' => 'help/portal-reference/features/hostings.md',
            'category' => 'portal-reference-infrastructure',
            'weight' => 10,
            'audience' => 'all',
            'searchable' => true,
        ],

        'vps' => [
            'title' => 'VPS',
            'file' => 'help/portal-reference/infrastructure/vps.md',
            'category' => 'portal-reference-infrastructure',
            'weight' => 20,
            'audience' => 'all',
            'searchable' => true,
        ],

        'voip' => [
            'title' => 'VoIP',
            'file' => 'help/portal-reference/infrastructure/voip.md',
            'category' => 'portal-reference-infrastructure',
            'weight' => 30,
            'audience' => 'all',
            'searchable' => true,
        ],

        'service-providers' => [
            'title' => 'Service Providers',
            'file' => 'help/portal-reference/infrastructure/service-providers.md',
            'category' => 'portal-reference-infrastructure',
            'weight' => 40,
            'audience' => 'all',
            'searchable' => true,
        ],

        'domain-emails' => [
            'title' => 'Domain Emails',
            'file' => 'help/portal-reference/infrastructure/domain-emails.md',
            'category' => 'portal-reference-infrastructure',
            'weight' => 50,
            'audience' => 'all',
            'searchable' => true,
        ],

        'other-services' => [
            'title' => 'Other Services',
            'file' => 'help/portal-reference/infrastructure/other-services.md',
            'category' => 'portal-reference-infrastructure',
            'weight' => 60,
            'audience' => 'all',
            'searchable' => true,
        ],

        'expiry-trackers' => [
            'title' => 'Expiry Trackers',
            'file' => 'help/portal-reference/infrastructure/expiry-trackers.md',
            'category' => 'portal-reference-infrastructure',
            'weight' => 70,
            'audience' => 'all',
            'searchable' => true,
        ],

        'assets' => [
            'title' => 'Assets',
            'file' => 'help/portal-reference/infrastructure/assets.md',
            'category' => 'portal-reference-infrastructure',
            'weight' => 80,
            'audience' => 'all',
            'searchable' => true,
        ],

        'g-mails' => [
            'title' => 'G·Mails',
            'file' => 'help/portal-reference/infrastructure/g-mails.md',
            'category' => 'portal-reference-infrastructure',
            'weight' => 90,
            'audience' => 'all',
            'searchable' => true,
        ],

        // ── Credentials & Vault ──────────────────────────────────────
        'vault' => [
            'title' => 'Vault',
            'file' => 'help/portal-reference/credentials/vault.md',
            'category' => 'portal-reference-credentials',
            'weight' => 0,
            'audience' => 'all',
            'searchable' => true,
        ],

        'credential-reveal' => [
            'title' => 'Credential Reveal',
            'file' => 'help/reference/credential-reveal.md',
            'category' => 'portal-reference-credentials',
            'weight' => 10,
            'audience' => 'all',
            'searchable' => true,
        ],

        // ── Operations ────────────────────────────────────────────────
        'monitoring' => [
            'title' => 'Monitoring',
            'file' => 'help/portal-reference/operations/monitoring.md',
            'category' => 'portal-reference-operations',
            'weight' => 0,
            'audience' => 'all',
            'searchable' => true,
        ],

        'tasks' => [
            'title' => 'Tasks',
            'file' => 'help/portal-reference/operations/tasks.md',
            'category' => 'portal-reference-operations',
            'weight' => 10,
            'audience' => 'all',
            'searchable' => true,
        ],

        'notes' => [
            'title' => 'Notes',
            'file' => 'help/portal-reference/operations/notes.md',
            'category' => 'portal-reference-operations',
            'weight' => 20,
            'audience' => 'all',
            'searchable' => true,
        ],

        'calendar' => [
            'title' => 'Calendar',
            'file' => 'help/portal-reference/operations/calendar.md',
            'category' => 'portal-reference-operations',
            'weight' => 30,
            'audience' => 'all',
            'searchable' => true,
        ],

        'global-search' => [
            'title' => 'Global Search',
            'file' => 'help/portal-reference/operations/global-search.md',
            'category' => 'portal-reference-operations',
            'weight' => 40,
            'audience' => 'all',
            'searchable' => true,
        ],

        // ── Administration (Super Admin only) ─────────────────────────
        'super-admin-guide' => [
            'title' => 'Super Admin Guide',
            'file' => 'help/administration/super-admin-guide.md',
            'category' => 'administration',
            'weight' => 0,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'users' => [
            'title' => 'Users',
            'file' => 'help/portal-reference/administrator/users.md',
            'category' => 'administration',
            'weight' => 10,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'roles' => [
            'title' => 'Roles',
            'file' => 'help/portal-reference/administrator/roles.md',
            'category' => 'administration',
            'weight' => 20,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'module-permissions' => [
            'title' => 'Module Permissions',
            'file' => 'help/portal-reference/administrator/module-permissions.md',
            'category' => 'administration',
            'weight' => 30,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'privileges' => [
            'title' => 'Privileges',
            'file' => 'help/portal-reference/administrator/privileges.md',
            'category' => 'administration',
            'weight' => 40,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'role-templates' => [
            'title' => 'Role Templates',
            'file' => 'help/portal-reference/administrator/role-templates.md',
            'category' => 'administration',
            'weight' => 50,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'activity-logs' => [
            'title' => 'Activity Logs',
            'file' => 'help/portal-reference/administrator/activity-logs.md',
            'category' => 'administration',
            'weight' => 60,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'login-audits' => [
            'title' => 'Login Audits',
            'file' => 'help/portal-reference/administrator/login-audits.md',
            'category' => 'administration',
            'weight' => 70,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'features' => [
            'title' => 'Features',
            'file' => 'help/portal-reference/administrator/features.md',
            'category' => 'administration',
            'weight' => 80,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'modules' => [
            'title' => 'Modules',
            'file' => 'help/portal-reference/administrator/modules.md',
            'category' => 'administration',
            'weight' => 90,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'import-export' => [
            'title' => 'Import / Export',
            'file' => 'help/portal-reference/administrator/import-export.md',
            'category' => 'administration',
            'weight' => 100,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'bulk-actions' => [
            'title' => 'Bulk Actions',
            'file' => 'help/portal-reference/administrator/bulk-actions.md',
            'category' => 'administration',
            'weight' => 110,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'reports' => [
            'title' => 'Reports',
            'file' => 'help/portal-reference/administrator/reports.md',
            'category' => 'administration',
            'weight' => 120,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'webhooks' => [
            'title' => 'Webhooks',
            'file' => 'help/portal-reference/administrator/webhooks.md',
            'category' => 'administration',
            'weight' => 130,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'smtp-profiles' => [
            'title' => 'SMTP Profiles',
            'file' => 'help/portal-reference/administrator/smtp-profiles.md',
            'category' => 'administration',
            'weight' => 140,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'api-tokens' => [
            'title' => 'API Tokens',
            'file' => 'help/portal-reference/administrator/api-tokens.md',
            'category' => 'administration',
            'weight' => 150,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        'attachments' => [
            'title' => 'Attachments',
            'file' => 'help/portal-reference/administrator/attachments.md',
            'category' => 'administration',
            'weight' => 160,
            'audience' => 'super-admin',
            'searchable' => true,
        ],

        // ── Troubleshooting & FAQ ─────────────────────────────────────
        'faq' => [
            'title' => 'Frequently Asked Questions',
            'file' => 'help/faq.md',
            'category' => 'troubleshooting',
            'weight' => 0,
            'audience' => 'all',
            'searchable' => true,
        ],

        // ── Reference ─────────────────────────────────────────────────
        'permission-reference' => [
            'title' => 'Permission Reference',
            'file' => 'help/reference/permission-reference.md',
            'category' => 'reference',
            'weight' => 0,
            'audience' => 'all',
            'searchable' => true,
        ],

        // ── Version History ───────────────────────────────────────────
        'changelog' => [
            'title' => 'Changelog',
            'file' => 'help/version-history/changelog.md',
            'category' => 'version-history',
            'weight' => 0,
            'audience' => 'all',
            'searchable' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module → Document Mapping
    |--------------------------------------------------------------------------
    |
    | Maps module slugs (used in contextual help triggers) to registered
    | document slugs. Contextual help renders the exact same documentation
    | source used by the full Help Center — no separate prose.
    |
    | Modules not listed here will return a "not available" response
    | when contextual help is requested.
    |
    */
    'module_doc_map' => [
        'dashboard'          => 'dashboard',
        'domains'            => 'domains',
        'hostings'           => 'hostings',
        'vps'                => 'vps',
        'voip'               => 'voip',
        'service-providers'  => 'service-providers',
        'domain-emails'      => 'domain-emails',
        'other-services'     => 'other-services',
        'expiry-trackers'    => 'expiry-trackers',
        'assets'             => 'assets',
        'g-mails'            => 'g-mails',
        'vault'              => 'vault',
        'tasks'              => 'tasks',
        'notes'              => 'notes',
        'calendar'           => 'calendar',
        'monitoring'         => 'monitoring',
        'users'              => 'users',
        'roles'              => 'roles',
        'module-permissions' => 'module-permissions',
        'activity-logs'      => 'activity-logs',
        'login-audits'       => 'login-audits',
        'smtp-profiles'      => 'smtp-profiles',
        'reports'            => 'reports',
        'notifications'      => 'quick-start',
        'role-templates'     => 'role-templates',
        'privileges'         => 'privileges',
        'modules'            => 'modules',
        'features'           => 'features',
        'import'             => 'import-export',
        'export'             => 'import-export',
        'attachments'        => 'attachments',
        'webhooks'           => 'webhooks',
        'tokens'             => 'api-tokens',
        'search'             => 'global-search',
        'profile'            => 'quick-start',
        'my-permissions'     => 'my-permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy Slug Redirect Map
    |--------------------------------------------------------------------------
    |
    | Maps old slugs to their replacement slugs. The controller resolves
    | these transparently by serving the target document content.
    |
    | Slugs that match a registered document key AND are in this map
    | are served directly (same-slug preservation).
    |
    */
    'legacy_slugs' => [
        'getting-started'  => 'quick-start',
        'permission-guide' => 'permission-reference',
        'monitoring-guide' => 'monitoring',
        'about'            => 'changelog',
    ],

    /*
    |--------------------------------------------------------------------------
    | Retired Slugs
    |--------------------------------------------------------------------------
    |
    | Slugs that were part of a previous Help Center version but have
    | no legitimate replacement. These return a 410 Gone response.
    |
    | Developer/internal slugs (architecture, developer-rbac,
    | disaster-recovery) are NOT listed here — they use a separate
    | resolution path and are never exposed through the production
    | Help Center registry.
    |
    */
    'retired_slugs' => [
        'my-role-guide',
        'daily-ops',
        'workflows',
        'troubleshooting',
        'release-notes',
        'admin-guide',
        'it-support-guide',
        'read-only-guide',
    ],
];
