<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VoipData',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'extensions', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
        new OA\Property(property: 'service_provider_id', type: 'integer', nullable: true),
        new OA\Property(property: 'phone_number', type: 'string', nullable: true),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'direction', type: 'string', nullable: true),
        new OA\Property(property: 'username', type: 'string', nullable: true),
        new OA\Property(property: 'dashboard_url', type: 'string', nullable: true),
        new OA\Property(property: 'server_ip', type: 'string', nullable: true),
        new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'status', type: 'string'),
        new OA\Property(property: 'number_status', type: 'string', nullable: true),
        new OA\Property(property: 'outbound_code', type: 'string', nullable: true),
        new OA\Property(property: 'team_details', type: 'string', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'module', ref: '#/components/schemas/ModuleData'),
        new OA\Property(property: 'user', type: 'object', nullable: true),
        new OA\Property(property: 'created_at', type: 'string'),
        new OA\Property(property: 'updated_at', type: 'string'),
    ],
    type: 'object'
)]
class VoipData {}
