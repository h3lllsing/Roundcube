<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DashboardData',
    properties: [
        new OA\Property(property: 'total_features', type: 'integer'),
        new OA\Property(property: 'total_modules', type: 'integer'),
        new OA\Property(property: 'tasks_by_status', type: 'object'),
        new OA\Property(property: 'total_tasks', type: 'integer'),
        new OA\Property(property: 'my_tasks_total', type: 'integer'),
        new OA\Property(property: 'my_pending_tasks', type: 'integer'),
        new OA\Property(property: 'total_notes', type: 'integer'),
        new OA\Property(property: 'my_notes', type: 'integer'),
        new OA\Property(property: 'unread_notifications', type: 'integer'),
        new OA\Property(property: 'total_notifications', type: 'integer'),
        new OA\Property(property: 'recent_activity', type: 'array', items: new OA\Items(type: 'object')),
        new OA\Property(property: 'total_users', type: 'integer'),
    ],
    type: 'object'
)]
class DashboardData
{
}
