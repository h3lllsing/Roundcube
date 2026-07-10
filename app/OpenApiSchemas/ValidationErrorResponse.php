<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ValidationErrorResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'errors', type: 'object'),
    ],
    type: 'object'
)]
class ValidationErrorResponse {}
