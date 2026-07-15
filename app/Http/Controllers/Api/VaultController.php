<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVaultRequest;
use App\Http\Requests\UpdateVaultRequest;
use App\Models\Module;
use App\Models\VaultEntry;
use App\Services\VaultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class VaultController extends Controller
{
    public function __construct(
        private readonly VaultService $vaultService
    ) {}

    #[OA\Get(
        path: '/my-vault',
        summary: 'List current user\'s vault entries (password always masked)',
        security: [['sanctum' => []]],
        tags: ['Password Vault'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['service_name', 'created_at', 'updated_at', 'username'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated vault entries (password masked)', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/VaultData')),
            ])),
        ]
    )]
    public function myVault(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'per_page', 'sort_by', 'sort_order']);
        $filters['user_id'] = $request->user()->id;

        $entries = $this->vaultService->list($filters);

        return response()->json($entries);
    }

    #[OA\Get(
        path: '/vault',
        summary: 'List vault entries (password always masked)',
        security: [['sanctum' => []]],
        tags: ['Password Vault'],
        parameters: [
            new OA\Parameter(name: 'module_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['service_name', 'created_at', 'updated_at', 'username'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'with_trashed', in: 'query', required: false, schema: new OA\Schema(type: 'boolean'), description: 'Super-admin only: include soft-deleted'),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated vault entries (password masked)', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/VaultData')),
            ])),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->only(['module_id', 'search', 'per_page', 'sort_by', 'sort_order']);

        if ($user->hasRole('super-admin') && $request->boolean('with_trashed')) {
            $filters['with_trashed'] = true;
        }

        if (! $user->hasRole('super-admin')) {
            $accessibleModuleIds = Module::whereHas('rolePermissions', function ($q) use ($user) {
                $q->whereIn('role_id', $user->roles()->pluck('roles.id'))->where('can_read', true);
            })->pluck('id');
            $filters['accessible_module_ids'] = $accessibleModuleIds;
            $filters['user_id'] = $user->id;
        }

        $entries = $this->vaultService->list($filters);

        return response()->json($entries);
    }

    #[OA\Post(
        path: '/vault',
        summary: 'Store a new password entry (encrypted at rest)',
        security: [['sanctum' => []]],
        tags: ['Password Vault'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'service_name', type: 'string'),
                new OA\Property(property: 'service_url', type: 'string', nullable: true),
                new OA\Property(property: 'username', type: 'string', nullable: true),
                new OA\Property(property: 'password', type: 'string', description: 'Plain text password — encrypted before storage'),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
                new OA\Property(property: 'description', type: 'string', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Vault entry created', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/VaultData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreVaultRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['user_id'] = $request->user()->id;
        $user = $request->user();
        if (!$user->hasRole('super-admin')) {
            $moduleId = $validated['module_id'] ?? null;
            abort_unless($moduleId && $user->canOnModule(Module::find($moduleId), 'create'), 403, 'Forbidden');
        }

        $entry = $this->vaultService->create($validated);

        return $this->created($entry->append('password_masked'), 'Vault entry created (password encrypted)');
    }

    #[OA\Get(
        path: '/vault/{id}',
        summary: 'Get vault entry details (password masked)',
        security: [['sanctum' => []]],
        tags: ['Password Vault'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Vault entry with masked password', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/VaultData'),
            ])),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Request $request, VaultEntry $vault): JsonResponse
    {
        if (! $request->user()->canAccessVault($vault)) {
            return $this->message('Forbidden', 403);
        }
        $vault->load('module.feature', 'user');

        return $this->success($vault->append('password_masked'));
    }

    #[OA\Post(
        path: '/vault/{id}/reveal',
        summary: 'Reveal the actual password (audit-logged)',
        security: [['sanctum' => []]],
        tags: ['Password Vault'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Password revealed (audit-logged)', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/VaultRevealData'),
            ])),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function reveal(Request $request, VaultEntry $vault): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->canRevealCredentialsFor($vault->module), 403);
        $password = $this->vaultService->reveal($vault, $user);

        return $this->success([
            'id' => $vault->id,
            'service_name' => $vault->service_name,
            'username' => $vault->username,
            'password' => $password,
        ], 'Password revealed (audit-logged)');
    }

    #[OA\Put(
        path: '/vault/{id}',
        summary: 'Update vault entry (re-encrypt if password changed)',
        security: [['sanctum' => []]],
        tags: ['Password Vault'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'service_name', type: 'string'),
                new OA\Property(property: 'service_url', type: 'string', nullable: true),
                new OA\Property(property: 'username', type: 'string', nullable: true),
                new OA\Property(property: 'password', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
                new OA\Property(property: 'description', type: 'string', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Vault entry updated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/VaultData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateVaultRequest $request, VaultEntry $vault): JsonResponse
    {
        if (! $request->user()->isVaultOwner($vault)) {
            return $this->message('Forbidden', 403);
        }
        $user = $request->user();
        if (!$user->hasRole('super-admin') && $vault->module && !$user->canOnModule($vault->module, 'update')) {
            abort(403, 'Forbidden');
        }
    $this->checkOptimisticLock($vault, $request);
    $validated = $request->validated();

    $entry = $this->vaultService->update($vault, $validated);

        return $this->success($entry->append('password_masked'), 'Vault entry updated');
    }

    #[OA\Delete(
        path: '/vault/{id}',
        summary: 'Soft-delete a vault entry',
        security: [['sanctum' => []]],
        tags: ['Password Vault'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Vault entry deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function destroy(Request $request, VaultEntry $vault): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole('super-admin'), 403);
        $this->vaultService->delete($vault);

        return $this->message('Vault entry deleted');
    }
}
