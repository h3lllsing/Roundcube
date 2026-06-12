<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    #[OA\Get(
        path: '/profile',
        summary: 'Get current user profile details',
        security: [['sanctum' => []]],
        tags: ['Profile'],
        responses: [
            new OA\Response(response: 200, description: 'Profile details', content: new OA\JsonContent(ref: '#/components/schemas/UserData')),
        ]
    )]
    public function show(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user()->load('roles');
        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('slug'),
            'permissions' => $user->getAllModulePermissions(),
        ]);
    }

    #[OA\Put(
        path: '/profile',
        summary: 'Update current user profile',
        security: [['sanctum' => []]],
        tags: ['Profile'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string', maxLength: 255),
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'current_password', type: 'string'),
                new OA\Property(property: 'password', type: 'string', minLength: 8),
                new OA\Property(property: 'password_confirmation', type: 'string'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Profile updated', content: new OA\JsonContent(ref: '#/components/schemas/UserData')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
        ]
    )]
    public function update(UpdateProfileRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $data = $request->only(['name', 'email']);

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        $user->load('roles');
        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('slug'),
            'permissions' => $user->getAllModulePermissions(),
        ], 'Profile updated');
    }
}
