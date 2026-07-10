<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LoginResponse',
    properties: [
        new OA\Property(property: 'token', type: 'string'),
        new OA\Property(property: 'user', ref: '#/components/schemas/UserData'),
    ],
    type: 'object'
)]
class LoginResponse {}
