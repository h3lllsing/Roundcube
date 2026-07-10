<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceProviderRequest;
use App\Http\Requests\UpdateServiceProviderRequest;
use App\Models\ServiceProvider;
use App\Services\ServiceProviderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ServiceProviderController extends Controller
{
    public function __construct(private readonly ServiceProviderService $serviceProviderService) {}

    #[OA\Get(
        path: '/service-providers',
        summary: 'List service providers',
        security: [['sanctum' => []]],
        tags: ['Service Providers'],
        parameters: [
            new OA\Parameter(name: 'module_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'expired', 'cancelled'])),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['name', 'type', 'provider', 'cost', 'status', 'expiry_date', 'created_at'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'with_trashed', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of service providers', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ServiceProviderData')),
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

        return response()->json($this->serviceProviderService->list($filters));
    }

    #[OA\Post(
        path: '/service-providers',
        summary: 'Create a service provider entry',
        security: [['sanctum' => []]],
        tags: ['Service Providers'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'type', type: 'string', enum: ['internet', 'hosting', 'email', 'telecom', 'other']),
                new OA\Property(property: 'provider', type: 'string', nullable: true),
                new OA\Property(property: 'website', type: 'string', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'status', type: 'string', default: 'active', enum: ['active', 'expired', 'cancelled']),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Service provider created', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/ServiceProviderData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreServiceProviderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $user = $request->user();
        if (!$user->hasRole('super-admin')) {
            $moduleId = $validated['module_id'] ?? null;
            abort_unless($moduleId && $user->canOnModule(\App\Models\Module::find($moduleId), 'create'), 403, 'Forbidden');
        }

        return $this->created($this->serviceProviderService->create($validated), 'Service provider created');
    }

    #[OA\Get(
        path: '/service-providers/{id}',
        summary: 'Get a service provider entry',
        security: [['sanctum' => []]],
        tags: ['Service Providers'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Service provider details', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/ServiceProviderData'),
            ])),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Request $request, ServiceProvider $serviceProvider): JsonResponse
    {
        $serviceProvider->load('module.feature', 'user');
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $serviceProvider->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }

        return $this->success($serviceProvider);
    }

    #[OA\Put(
        path: '/service-providers/{id}',
        summary: 'Update a service provider entry',
        security: [['sanctum' => []]],
        tags: ['Service Providers'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'type', type: 'string', enum: ['internet', 'hosting', 'email', 'telecom', 'other']),
                new OA\Property(property: 'provider', type: 'string', nullable: true),
                new OA\Property(property: 'website', type: 'string', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'expired', 'cancelled']),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Service provider updated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/ServiceProviderData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateServiceProviderRequest $request, ServiceProvider $serviceProvider): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $serviceProvider->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        if (!$user->hasRole('super-admin') && $serviceProvider->module && !$user->canOnModule($serviceProvider->module, 'update')) {
            abort(403, 'Forbidden');
        }
        $this->checkOptimisticLock($serviceProvider, $request);

        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $this->success($this->serviceProviderService->update($serviceProvider, $data), 'Service provider updated');
    }

    #[OA\Delete(
        path: '/service-providers/{id}',
        summary: 'Soft-delete a service provider entry',
        security: [['sanctum' => []]],
        tags: ['Service Providers'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Service provider deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function destroy(Request $request, ServiceProvider $serviceProvider): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $serviceProvider->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        if (!$user->hasRole('super-admin') && $serviceProvider->module && !$user->canOnModule($serviceProvider->module, 'delete')) {
            abort(403, 'Forbidden');
        }
        $this->serviceProviderService->delete($serviceProvider);

        return $this->message('Service provider deleted');
    }
}
