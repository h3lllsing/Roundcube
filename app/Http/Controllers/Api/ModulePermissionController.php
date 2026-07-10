<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreModulePermissionRequest;
use App\Models\Module;
use App\Models\User;
use App\Services\ModulePermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class ModulePermissionController extends Controller
{
    public function __construct(
        private readonly ModulePermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/modules/{module}/permissions',
        summary: 'List role permissions for a module',
        security: [['sanctum' => []]],
        tags: ['Permissions'],
        parameters: [
            new OA\Parameter(name: 'module', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of role permissions', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(properties: [
                    new OA\Property(property: 'module_id', type: 'integer'),
                    new OA\Property(property: 'role_id', type: 'integer'),
                    new OA\Property(property: 'role_name', type: 'string'),
                    new OA\Property(property: 'can_create', type: 'boolean'),
                    new OA\Property(property: 'can_read', type: 'boolean'),
                    new OA\Property(property: 'can_update', type: 'boolean'),
                    new OA\Property(property: 'can_delete', type: 'boolean'),
                    new OA\Property(property: 'can_export', type: 'boolean'),
                ], type: 'object')),
            ])),
        ]
    )]
    public function index(Module $module): JsonResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $permissions = $this->permissionService->getForModule($module);

        return $this->success($permissions);
    }

    #[OA\Post(
        path: '/modules/{module}/permissions',
        summary: 'Create or update permissions for a role on a module (upsert)',
        security: [['sanctum' => []]],
        tags: ['Permissions'],
        parameters: [
            new OA\Parameter(name: 'module', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'role_id', type: 'integer'),
                new OA\Property(property: 'can_create', type: 'boolean', default: false),
                new OA\Property(property: 'can_read', type: 'boolean', default: false),
                new OA\Property(property: 'can_update', type: 'boolean', default: false),
                new OA\Property(property: 'can_delete', type: 'boolean', default: false),
        
                new OA\Property(property: 'can_export', type: 'boolean', default: false),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Permissions updated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', properties: [
                    new OA\Property(property: 'module_id', type: 'integer'),
                    new OA\Property(property: 'role_id', type: 'integer'),
                    new OA\Property(property: 'can_create', type: 'boolean'),
                    new OA\Property(property: 'can_read', type: 'boolean'),
                    new OA\Property(property: 'can_update', type: 'boolean'),
                    new OA\Property(property: 'can_delete', type: 'boolean'),
                    new OA\Property(property: 'can_approve', type: 'boolean'),
                    new OA\Property(property: 'can_export', type: 'boolean'),
                ], type: 'object'),
            ])),
        ]
    )]
    public function store(StoreModulePermissionRequest $request, Module $module): JsonResponse
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);

        $permission = $this->permissionService->setForRole(
            $module,
            $request->role_id,
            $request->only(config('permissions.keys'))
        );

        return $this->success($permission, 'Permissions updated');
    }

    #[OA\Delete(
        path: '/modules/{module}/permissions/{roleId}',
        summary: 'Remove all permissions for a role on a module',
        security: [['sanctum' => []]],
        tags: ['Permissions'],
        parameters: [
            new OA\Parameter(name: 'module', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'roleId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Role permissions removed', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
            ])),
        ]
    )]
    public function destroy(Module $module, int $roleId): JsonResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $this->permissionService->removeForRole($module, $roleId);

        return $this->message('Role permissions removed');
    }

    #[OA\Get(
        path: '/modules/{module}/my-permissions',
        summary: 'Get current user permissions for a specific module',
        security: [['sanctum' => []]],
        tags: ['Permissions'],
        parameters: [
            new OA\Parameter(name: 'module', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User permissions for module', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', properties: [
                    new OA\Property(property: 'can_create', type: 'boolean'),
                    new OA\Property(property: 'can_read', type: 'boolean'),
                    new OA\Property(property: 'can_update', type: 'boolean'),
                    new OA\Property(property: 'can_delete', type: 'boolean'),
                    new OA\Property(property: 'can_export', type: 'boolean'),
                ], type: 'object'),
            ])),
        ]
    )]
    public function userPermissions(Request $request, Module $module): JsonResponse
    {
        $user = $request->user();
        $perms = $this->permissionService->getUserPermissionsForModule($module, $user);

        return $this->success($perms);
    }

    #[OA\Get(
        path: '/my/module-permissions',
        summary: 'Get current user permissions across all modules',
        security: [['sanctum' => []]],
        tags: ['Permissions'],
        responses: [
            new OA\Response(response: 200, description: 'All module permissions for user'),
        ]
    )]
    #[OA\Get(
        path: '/users/{user}/module-permissions',
        summary: 'Get a specific user permissions across all modules (admin)',
        security: [['sanctum' => []]],
        tags: ['Permissions'],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'All module permissions for specified user'),
        ]
    )]
    public function userAllPermissions(Request $request, ?User $user = null): JsonResponse
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);

        $user = $user ?? $request->user();
        $roleIds = $user->roles()->pluck('roles.id');
        $allPermissions = [];

        $modules = Module::whereHas('rolePermissions', function ($q) use ($roleIds) {
            $q->whereIn('role_id', $roleIds);
        })->with(['feature', 'rolePermissions' => function ($q) use ($roleIds) {
            $q->whereIn('role_id', $roleIds);
        }])->get();

        foreach ($modules as $module) {
            $merged = [
                'can_create' => false, 'can_read' => false, 'can_update' => false,
                'can_delete' => false, 'can_export' => false,
            ];
            foreach ($module->rolePermissions as $rp) {
                foreach ($merged as $key => &$val) {
                    if ($rp->$key) {
                        $val = true;
                    }
                }
            }
            $allPermissions[] = [
                'module_id' => $module->id,
                'module_name' => $module->name,
                'feature' => $module->feature->name ?? null,
                'permissions' => $merged,
            ];
        }

        return $this->success($allPermissions);
    }
}
