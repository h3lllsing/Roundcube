<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ReportData',
    properties: [
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'group_by', type: 'string'),
        new OA\Property(property: 'date_from', type: 'string', format: 'date'),
        new OA\Property(property: 'date_to', type: 'string', format: 'date'),
        new OA\Property(property: 'periods', type: 'array', items: new OA\Items(type: 'object')),
        new OA\Property(property: 'summary', type: 'object'),
    ],
    type: 'object'
)]
class ReportData {}
