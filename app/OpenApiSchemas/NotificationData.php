<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotificationData',
    properties: [
        new OA\Property(property: 'id', type: 'string'),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'data', type: 'object'),
        new OA\Property(property: 'read_at', type: 'string', nullable: true),
        new OA\Property(property: 'created_at', type: 'string'),
    ],
    type: 'object'
)]
class NotificationData
{
}
