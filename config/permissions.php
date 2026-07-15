<?php

return [

    'keys' => [
        'can_create', 'can_read', 'can_update', 'can_delete',
        'can_approve', 'can_export', 'can_reveal', 'can_import',
    ],

    'sensitive_modules' => [
        'domains',
        'hostings',
        'vps',
        'users',
        'api-tokens',
    ],

    'sensitive_permissions' => [
        'can_delete',
        'can_reveal',
        'can_import',
    ],

    'importable_modules' => [
        'domains', 'hostings', 'vps', 'voip', 'service-providers',
        'domain-emails', 'other-services', 'expiry-trackers', 'assets', 'g-mails',
        'tasks', 'notes', 'vault', 'activity-logs', 'login-audits',
        'users', 'roles', 'privileges', 'attachments',
        'webhooks', 'tokens',
    ],

    'exportable_modules' => [
        'domains', 'hostings', 'vps', 'voip', 'service-providers',
        'domain-emails', 'other-services', 'expiry-trackers', 'assets', 'g-mails',
        'tasks', 'notes', 'vault', 'activity-logs', 'login-audits',
        'users', 'roles', 'privileges', 'attachments',
        'webhooks', 'tokens',
    ],
];
