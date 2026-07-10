<?php

namespace App\OpenApiSchemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AttachmentData',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'user_id', type: 'integer'),
        new OA\Property(property: 'notable_type', type: 'string', nullable: true),
        new OA\Property(property: 'notable_id', type: 'integer', nullable: true),
        new OA\Property(property: 'filename', type: 'string'),
        new OA\Property(property: 'original_name', type: 'string'),
        new OA\Property(property: 'mime_type', type: 'string'),
        new OA\Property(property: 'size', type: 'integer'),
        new OA\Property(property: 'created_at', type: 'string'),
        new OA\Property(property: 'updated_at', type: 'string'),
    ],
    type: 'object'
)]
class AttachmentData {}
