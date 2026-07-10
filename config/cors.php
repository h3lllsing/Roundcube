<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'docs', 'api/documentation'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'X-CSRF-TOKEN', 'X-Socket-Id'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
