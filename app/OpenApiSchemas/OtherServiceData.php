<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OtherServiceData',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'service_type', type: 'string'),
        new OA\Property(property: 'service_provider_id', type: 'integer', nullable: true),
        new OA\Property(property: 'username', type: 'string', nullable: true),
        new OA\Property(property: 'login_url', type: 'string', nullable: true),
        new OA\Property(property: 'website', type: 'string', nullable: true),
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
class OtherServiceData {}
