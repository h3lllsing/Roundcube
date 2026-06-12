<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VaultRevealData',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'service_name', type: 'string'),
        new OA\Property(property: 'service_url', type: 'string', nullable: true),
        new OA\Property(property: 'username', type: 'string', nullable: true),
        new OA\Property(property: 'password', type: 'string'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
    ],
    type: 'object'
)]
class VaultRevealData
{
}
