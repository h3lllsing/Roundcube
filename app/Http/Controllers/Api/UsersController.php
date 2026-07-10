<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserManagementService;
use App\Services\UserPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UsersController extends Controller
{
    public function __construct(
        private readonly UserManagementService $userManagementService,
        private readonly UserPermissionService $permissionService,
    ) {}

    #[OA\Get(
        path: '/users',
        summary: 'List users (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Users'],
    )]
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);

        $filters = $request->only(['search', 'with_trashed', 'trashed_only', 'sort_by', 'sort_order', 'per_page']);
        $users = $this->userManagementService->list($filters);

        return response()->json($users);
    }

    #[OA\Post(
        path: '/users',
        summary: 'Create a user (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Users'],
    )]
    public function store(StoreUserRequest $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);

        $user = $this->userManagementService->create($request->validated());

        return $this->created($user, 'User created');
    }

    #[OA\Get(
        path: '/users/{id}',
        summary: 'Get a user (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Users'],
    )]
    public function show(User $user): JsonResponse
    {
        abort_unless(request()->user()->hasRole('super-admin'), 403);

        $user->loadMissing('roles');

        return $this->success($user);
    }

    #[OA\Put(
        path: '/users/{id}',
        summary: 'Update a user (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Users'],
    )]
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $currentUser = $request->user();
        abort_unless($currentUser->hasRole('super-admin'), 403);

        $this->permissionService->preventSuperAdminAssignment($request);

        $superAdminRoleId = $this->permissionService->getSuperAdminRoleId();
        if ($currentUser->id === $user->id && $superAdminRoleId && $request->has('roles')) {
            $currentRoles = $user->roles()->pluck('roles.id')->toArray();
            if (in_array($superAdminRoleId, $currentRoles) && ! in_array($superAdminRoleId, (array) $request->input('roles'))) {
                abort(403, 'Cannot remove your own Super Admin role.');
            }
        }

        $this->checkOptimisticLock($user, $request);
        $updatedUser = $this->userManagementService->update($user, $request->validated(), $currentUser);

        return $this->success($updatedUser, 'User updated');
    }

    #[OA\Delete(
        path: '/users/{id}',
        summary: 'Soft-delete a user (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Users'],
    )]
    public function destroy(User $user): JsonResponse
    {
        abort_unless(request()->user()->hasRole('super-admin'), 403);

        $this->userManagementService->delete($user);

        return $this->message('User deleted');
    }

    #[OA\Patch(
        path: '/users/{id}/suspend',
        summary: 'Toggle user suspend/unsuspend (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Users'],
    )]
    public function suspend(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);

        $this->userManagementService->suspend($user, $request->user());

        return $this->message('User suspended');
    }

    public function unsuspend(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);

        $this->userManagementService->unsuspend($user, $request->user());

        return $this->message('User unsuspended');
    }
}
