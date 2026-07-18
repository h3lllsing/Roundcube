<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class MonitorController extends Controller
{
    public function check(string $type, int $id): \Illuminate\Http\JsonResponse
    {
        return response()->json(['error' => 'Monitoring is not available for this resource type.'], 400);
    }
}
