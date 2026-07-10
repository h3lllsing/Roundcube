<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TaskCounts',
    properties: [
        new OA\Property(property: 'total', type: 'integer'),
        new OA\Property(property: 'pending', type: 'integer'),
        new OA\Property(property: 'in_progress', type: 'integer'),
        new OA\Property(property: 'completed', type: 'integer'),
        new OA\Property(property: 'cancelled', type: 'integer'),
    ],
    type: 'object'
)]
class TaskCounts {}
