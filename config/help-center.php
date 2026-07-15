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
    | Only existing documents are registered. Batch 1 contains minimal
    | foundation documents; additional documents will be added in later
    | implementation batches.
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

        // ── Features ──────────────────────────────────────────────────
        'dashboard' => [
            'title' => 'Dashboard',
            'file' => 'help/portal-reference/features/dashboard.md',
            'category' => 'portal-reference-features',
            'weight' => 0,
            'audience' => 'all',
            'searchable' => true,
        ],

        'domains' => [
            'title' => 'Domains',
            'file' => 'help/portal-reference/features/domains.md',
            'category' => 'portal-reference-features',
            'weight' => 10,
            'audience' => 'all',
            'searchable' => true,
        ],

        'hostings' => [
            'title' => 'Hostings',
            'file' => 'help/portal-reference/features/hostings.md',
            'category' => 'portal-reference-features',
            'weight' => 20,
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

        // ── Administration (Super Admin only) ─────────────────────────
        'super-admin-guide' => [
            'title' => 'Super Admin Guide',
            'file' => 'help/administration/super-admin-guide.md',
            'category' => 'administration',
            'weight' => 0,
            'audience' => 'super-admin',
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
        'vps'                => 'domains',
        'voip'               => 'domains',
        'service-providers'  => 'domains',
        'domain-emails'      => 'domains',
        'other-services'     => 'domains',
        'expiry-trackers'    => 'domains',
        'assets'             => 'domains',
        'g-mails'            => 'domains',
        'vault'              => 'domains',
        'tasks'              => 'domains',
        'notes'              => 'domains',
        'calendar'           => 'quick-start',
        'monitoring'         => 'monitoring',
        'users'              => 'super-admin-guide',
        'roles'              => 'super-admin-guide',
        'module-permissions' => 'super-admin-guide',
        'activity-logs'      => 'super-admin-guide',
        'smtp-profiles'      => 'super-admin-guide',
        'reports'            => 'super-admin-guide',
        'notifications'      => 'quick-start',
        'role-templates'     => 'super-admin-guide',
        'privileges'         => 'super-admin-guide',
        'modules'            => 'super-admin-guide',
        'features'           => 'super-admin-guide',
        'import'             => 'super-admin-guide',
        'attachments'        => 'super-admin-guide',
        'webhooks'           => 'super-admin-guide',
        'tokens'             => 'super-admin-guide',
        'login-audits'       => 'super-admin-guide',
        'search'             => 'quick-start',
        'export'             => 'domains',
        'profile'            => 'quick-start',
        'my-permissions'     => 'permission-reference',
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
        'faq',
        'troubleshooting',
        'release-notes',
        'admin-guide',
        'it-support-guide',
        'read-only-guide',
    ],
];
