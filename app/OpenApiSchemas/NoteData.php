<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NoteData',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'content', type: 'string'),
        new OA\Property(property: 'user', type: 'object'),
        new OA\Property(property: 'notable_type', type: 'string'),
        new OA\Property(property: 'notable_id', type: 'integer', nullable: true),
        new OA\Property(property: 'created_at', type: 'string'),
        new OA\Property(property: 'updated_at', type: 'string'),
    ],
    type: 'object'
)]
class NoteData
{
}
