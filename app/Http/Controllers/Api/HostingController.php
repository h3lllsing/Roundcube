<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHostingRequest;
use App\Http\Requests\UpdateHostingRequest;
use App\Models\Hosting;
use App\Services\HostingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class HostingController extends Controller
{
    public function __construct(private readonly HostingService $hostingService) {}

    #[OA\Get(
        path: '/hostings',
        summary: 'List hosting entries',
        security: [['sanctum' => []]],
        tags: ['Hostings'],
        parameters: [
            new OA\Parameter(name: 'module_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'inactive', 'expired', 'suspended', 'pending_transfer', 'cancelled'])),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['name', 'provider', 'plan', 'expiry_date', 'cost', 'status', 'created_at'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'with_trashed', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of hostings', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/HostingData')),
            ])),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->only(['module_id', 'search', 'status', 'per_page', 'sort_by', 'sort_order']);
        if ($user->hasRole('super-admin') && $request->boolean('with_trashed')) {
            $filters['with_trashed'] = true;
        }
        if (! $user->hasRole('super-admin')) {
            $ids = $user->getAccessibleModuleIds('read');
            $filters['accessible_module_ids'] = $ids ?: [0];
        }

        return response()->json($this->hostingService->list($filters));
    }

    #[OA\Post(
        path: '/hostings',
        summary: 'Create a hosting entry',
        security: [['sanctum' => []]],
        tags: ['Hostings'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'username', type: 'string', nullable: true),
                new OA\Property(property: 'password', type: 'string', nullable: true),
                new OA\Property(property: 'cpanel_url', type: 'string', format: 'uri', nullable: true),
                new OA\Property(property: 'provider', type: 'string', nullable: true),
                new OA\Property(property: 'plan', type: 'string', nullable: true),
                new OA\Property(property: 'domain', type: 'string', nullable: true),
                new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'status', type: 'string', default: 'active', enum: ['active', 'inactive', 'expired', 'suspended', 'pending_transfer', 'cancelled']),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Hosting created', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/HostingData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreHostingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $user = $request->user();
        if (!$user->hasRole('super-admin')) {
            $moduleId = $validated['module_id'] ?? null;
            abort_unless($moduleId && $user->canOnModule(\App\Models\Module::find($moduleId), 'create'), 403, 'Forbidden');
        }

        return $this->created($this->hostingService->create($validated), 'Hosting created');
    }

    #[OA\Get(
        path: '/hostings/{id}',
        summary: 'Get a hosting entry',
        security: [['sanctum' => []]],
        tags: ['Hostings'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Hosting details', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/HostingData'),
            ])),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Request $request, Hosting $hosting): JsonResponse
    {
        $hosting->load('module.feature', 'user');
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $hosting->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }

        return $this->success($hosting);
    }

    #[OA\Put(
        path: '/hostings/{id}',
        summary: 'Update a hosting entry',
        security: [['sanctum' => []]],
        tags: ['Hostings'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'username', type: 'string', nullable: true),
                new OA\Property(property: 'password', type: 'string', nullable: true),
                new OA\Property(property: 'cpanel_url', type: 'string', format: 'uri', nullable: true),
                new OA\Property(property: 'provider', type: 'string', nullable: true),
                new OA\Property(property: 'plan', type: 'string', nullable: true),
                new OA\Property(property: 'domain', type: 'string', nullable: true),
                new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'expired', 'suspended', 'pending_transfer', 'cancelled']),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Hosting updated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/HostingData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateHostingRequest $request, Hosting $hosting): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $hosting->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        if (!$user->hasRole('super-admin') && $hosting->module && !$user->canOnModule($hosting->module, 'update')) {
            abort(403, 'Forbidden');
        }
        $this->checkOptimisticLock($hosting, $request);

        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $this->success($this->hostingService->update($hosting, $data), 'Hosting updated');
    }

    #[OA\Delete(
        path: '/hostings/{id}',
        summary: 'Soft-delete a hosting entry',
        security: [['sanctum' => []]],
        tags: ['Hostings'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Hosting deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function destroy(Request $request, Hosting $hosting): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $hosting->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        if (!$user->hasRole('super-admin') && $hosting->module && !$user->canOnModule($hosting->module, 'delete')) {
            abort(403, 'Forbidden');
        }
        $this->hostingService->delete($hosting);

        return $this->message('Hosting deleted');
    }
}
