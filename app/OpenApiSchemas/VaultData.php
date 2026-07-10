<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VaultData',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'service_name', type: 'string'),
        new OA\Property(property: 'service_url', type: 'string', nullable: true),
        new OA\Property(property: 'username', type: 'string', nullable: true),
        new OA\Property(property: 'password_masked', type: 'string'),
        new OA\Property(property: 'module', ref: '#/components/schemas/ModuleData'),
        new OA\Property(property: 'created_by', type: 'object', nullable: true),
        new OA\Property(property: 'created_at', type: 'string'),
        new OA\Property(property: 'updated_at', type: 'string'),
    ],
    type: 'object'
)]
class VaultData {}
