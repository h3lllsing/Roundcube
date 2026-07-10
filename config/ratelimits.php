<?php

return [
    'api' => env('API_RATE_LIMIT', 60),
    'search' => env('SEARCH_RATE_LIMIT', 20),
    'export' => env('EXPORT_RATE_LIMIT', 5),
    'bulk' => env('BULK_RATE_LIMIT', 10),
    'import' => env('IMPORT_RATE_LIMIT', 5),
];
