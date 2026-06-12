<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function success(mixed $data, ?string $message = null, int $status = 200): JsonResponse
    {
        $payload = ['data' => $data];
        if ($message) {
            $payload['message'] = $message;
        }
        return response()->json($payload, $status);
    }

    protected function created(mixed $data, ?string $message = null): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function message(string $message, int $status = 200): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
