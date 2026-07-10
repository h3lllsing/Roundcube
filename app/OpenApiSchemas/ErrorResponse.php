<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string'),
    ],
    type: 'object'
)]
class ErrorResponse {}
