<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TokenController extends Controller
{
    #[OA\Get(
        path: '/tokens',
        summary: 'List API tokens for the authenticated user',
        security: [['sanctum' => []]],
        tags: ['Tokens'],
        responses: [new OA\Response(response: 200, description: 'List of tokens')]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->tokens()->orderByDesc('id');

        $request->validate(['search' => 'nullable|string|max:255']);
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $tokens = $query->get()->map(fn ($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'created_at' => $t->created_at,
            'last_used_at' => $t->last_used_at,
        ]);

        return $this->success($tokens);
    }

    #[OA\Post(
        path: '/tokens',
        summary: 'Create a new API token',
        security: [['sanctum' => []]],
        tags: ['Tokens'],
        responses: [new OA\Response(response: 200, description: 'Token created')]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255']);

        $token = $request->user()->createToken($request->name);

        return $this->success([
            'id' => $token->accessToken->id,
            'name' => $token->accessToken->name,
            'created_at' => $token->accessToken->created_at,
            'plain_text' => $token->plainTextToken,
        ], 'Token created successfully');
    }

    #[OA\Delete(
        path: '/tokens/{id}',
        summary: 'Revoke an API token',
        security: [['sanctum' => []]],
        tags: ['Tokens'],
        responses: [new OA\Response(response: 200, description: 'Token revoked')]
    )]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $deleted = $request->user()->tokens()->where('id', $id)->delete();

        if (! $deleted) {
            return $this->message('Token not found', 404);
        }

        return $this->message('Token revoked successfully');
    }
}
