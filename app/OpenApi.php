<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'OpsPilot API',
    description: 'Enterprise permission-based RBAC API with features, modules, tasks, notes, activity logs, and notifications.',
    contact: new OA\Contact(email: 'admin@tyro.project'),
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST . '/api',
    description: 'API Server',
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum',
    description: 'Enter Sanctum token. Get it by logging in.'
)]
class OpenApi {}
