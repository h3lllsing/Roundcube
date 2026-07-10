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
];
