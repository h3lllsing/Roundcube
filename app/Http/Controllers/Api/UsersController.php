<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UsersController extends Controller
{
    #[OA\Get(
        path: '/users',
        summary: 'List users (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'with_trashed', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'trashed_only', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['name', 'email', 'created_at'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of users'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = User::query();

        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        } elseif ($request->boolean('trashed_only')) {
            $query->onlyTrashed();
        }

        $sortBy = in_array($request->get('sort_by'), ['name', 'email', 'created_at']) ? $request->get('sort_by') : 'created_at';
        $sortOrder = in_array($request->get('sort_order'), ['asc', 'desc']) ? $request->get('sort_order') : 'desc';

        $query->with('roles')->orderBy($sortBy, $sortOrder);

        $perPage = min((int) $request->get('per_page', 20), 100);

        $users = $query->paginate($perPage);

        return response()->json($users);
    }

    #[OA\Post(
        path: '/users',
        summary: 'Create a user (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Users'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'password', type: 'string', format: 'password'),
                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'User created'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreUserRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        if (!empty($data['roles'])) {
            $roles = Role::whereIn('id', $data['roles'])->get();
            $user->roles()->sync($roles);
        }

        $user->loadMissing('roles');

        return $this->created($user, 'User created');
    }

    #[OA\Get(
        path: '/users/{id}',
        summary: 'Get a user (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User details'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(User $user): \Illuminate\Http\JsonResponse
    {
        $user->loadMissing('roles');

        return $this->success($user);
    }

    #[OA\Put(
        path: '/users/{id}',
        summary: 'Update a user (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'password', type: 'string', format: 'password', nullable: true),
                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'User updated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateUserRequest $request, User $user): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }
        if (isset($data['email'])) {
            $user->email = $data['email'];
        }
        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        if (isset($data['roles'])) {
            $roles = Role::whereIn('id', $data['roles'])->get();
            $user->roles()->sync($roles);
        }

        $user->loadMissing('roles');

        return $this->success($user, 'User updated');
    }

    #[OA\Delete(
        path: '/users/{id}',
        summary: 'Soft-delete a user (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function destroy(User $user): \Illuminate\Http\JsonResponse
    {
        $user->delete();

        return $this->message('User deleted');
    }

    #[OA\Patch(
        path: '/users/{id}/suspend',
        summary: 'Toggle user suspend/unsuspend (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User suspended or unsuspended'),
        ]
    )]
    public function suspend(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        if ($user->suspended_at) {
            $user->update(['suspended_at' => null]);
            return $this->message('User unsuspended');
        }

        $user->update(['suspended_at' => now()]);
        return $this->message('User suspended');
    }
}
