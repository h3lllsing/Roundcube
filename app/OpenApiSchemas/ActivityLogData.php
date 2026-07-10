<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ActivityLogData',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'log_name', type: 'string'),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'event', type: 'string', nullable: true),
        new OA\Property(property: 'subject_type', type: 'string'),
        new OA\Property(property: 'subject_id', type: 'integer', nullable: true),
        new OA\Property(property: 'subject', type: 'object', nullable: true),
        new OA\Property(property: 'causer', type: 'object', nullable: true),
        new OA\Property(property: 'properties', type: 'object', nullable: true),
        new OA\Property(property: 'created_at', type: 'string'),
    ],
    type: 'object'
)]
class ActivityLogData {}
