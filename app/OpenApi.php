<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Tyro RBAC API',
    description: 'Enterprise permission-based RBAC API with features, modules, tasks, notes, activity logs, and notifications.',
    contact: new OA\Contact(email: 'admin@tyro.project'),
)]
#[OA\Server(
    url: 'http://localhost:8000/api',
    description: 'Local Development Server',
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum',
    description: 'Enter Sanctum token. Get it by logging in.'
)]
class OpenApi
{
}
