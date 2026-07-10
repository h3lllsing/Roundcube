<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function checkOptimisticLock(\Illuminate\Database\Eloquent\Model $model, \Illuminate\Http\Request $request): void
    {
        $submittedUpdatedAt = $request->input('updated_at');
        if ($submittedUpdatedAt === null) {
            abort(409, 'Optimistic lock field (updated_at) is required.');
        }
        $currentUpdatedAt = $model->updated_at;
        try {
            $submitted = \Carbon\Carbon::parse($submittedUpdatedAt);
        } catch (\Exception $e) {
            abort(422, 'Invalid updated_at format.');
        }
        if ($submitted->format('Y-m-d H:i:s') !== $currentUpdatedAt->format('Y-m-d H:i:s')) {
            abort(409, 'The record was modified by another user. Please refresh and try again.');
        }
    }

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
