<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DomainData',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'registrar', type: 'string', nullable: true),
        new OA\Property(property: 'registration_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'auto_renew', type: 'boolean'),
        new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'status', type: 'string'),
        new OA\Property(property: 'dns_servers', type: 'string', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'module', ref: '#/components/schemas/ModuleData'),
        new OA\Property(property: 'user', type: 'object', nullable: true),
        new OA\Property(property: 'created_at', type: 'string'),
        new OA\Property(property: 'updated_at', type: 'string'),
    ],
    type: 'object'
)]
class DomainData {}
