<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VpsData',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'provider', type: 'string', nullable: true),
        new OA\Property(property: 'plan', type: 'string', nullable: true),
        new OA\Property(property: 'ip_address', type: 'string', nullable: true),
        new OA\Property(property: 'os', type: 'string', nullable: true),
        new OA\Property(property: 'ram_mb', type: 'integer', nullable: true),
        new OA\Property(property: 'disk_gb', type: 'integer', nullable: true),
        new OA\Property(property: 'cpu_cores', type: 'integer', nullable: true),
        new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'status', type: 'string'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'module', ref: '#/components/schemas/ModuleData'),
        new OA\Property(property: 'user', type: 'object', nullable: true),
        new OA\Property(property: 'created_at', type: 'string'),
        new OA\Property(property: 'updated_at', type: 'string'),
    ],
    type: 'object'
)]
class VpsData {}
