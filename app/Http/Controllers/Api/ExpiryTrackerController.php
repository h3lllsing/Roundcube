<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpiryTrackerRequest;
use App\Http\Requests\UpdateExpiryTrackerRequest;
use App\Models\ExpiryTracker;
use App\Services\ExpiryTrackerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ExpiryTrackerController extends Controller
{
    public function __construct(
        private readonly ExpiryTrackerService $expiryTrackerService
    ) {}

    #[OA\Get(
        path: '/expiry-trackers',
        summary: 'List renewal items',
        security: [['sanctum' => []]],
        tags: ['Renewals'],
        parameters: [
            new OA\Parameter(name: 'module_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'expired', 'pending_renewal', 'cancelled'])),
            new OA\Parameter(name: 'expiring_soon', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'expired', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'date_from', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['name', 'expiry_date', 'cost', 'status', 'created_at'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'with_trashed', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of expiry trackers', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ExpiryTrackerData')),
            ])),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->only(['module_id', 'search', 'status', 'per_page', 'sort_by', 'sort_order', 'expiring_soon', 'expired', 'date_from', 'date_to']);

        if ($user->hasRole('super-admin') && $request->boolean('with_trashed')) {
            $filters['with_trashed'] = true;
        }

        if (! $user->hasRole('super-admin')) {
            $ids = $user->getAccessibleModuleIds('read');
            $filters['accessible_module_ids'] = $ids ?: [0];
        }

        $entries = $this->expiryTrackerService->list($filters);

        return response()->json($entries);
    }

    #[OA\Post(
        path: '/expiry-trackers',
        summary: 'Create a renewal entry',
        security: [['sanctum' => []]],
        tags: ['Renewals'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'service_provider_id', type: 'integer', nullable: true),
                new OA\Property(property: 'username', type: 'string', nullable: true),
                new OA\Property(property: 'login_url', type: 'string', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'renewal_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'status', type: 'string', default: 'active', enum: ['active', 'expired', 'pending_renewal', 'cancelled']),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Expiry tracker created', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/ExpiryTrackerData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreExpiryTrackerRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $user = $request->user();
        if (!$user->hasRole('super-admin')) {
            $moduleId = $validated['module_id'] ?? null;
            abort_unless($moduleId && $user->canOnModule(\App\Models\Module::find($moduleId), 'create'), 403, 'Forbidden');
        }
        $entry = $this->expiryTrackerService->create($validated);

        return $this->created($entry, 'Renewal created');
    }

    #[OA\Get(
        path: '/expiry-trackers/{id}',
        summary: 'Get a renewal entry',
        security: [['sanctum' => []]],
        tags: ['Renewals'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Renewal details', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/ExpiryTrackerData'),
            ])),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Request $request, ExpiryTracker $expiryTracker): JsonResponse
    {
        $expiryTracker->load('module.feature', 'user');
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $expiryTracker->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }

        return $this->success($expiryTracker);
    }

    #[OA\Put(
        path: '/expiry-trackers/{id}',
        summary: 'Update a renewal entry',
        security: [['sanctum' => []]],
        tags: ['Renewals'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'service_provider_id', type: 'integer', nullable: true),
                new OA\Property(property: 'username', type: 'string', nullable: true),
                new OA\Property(property: 'login_url', type: 'string', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'renewal_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'expired', 'pending_renewal', 'cancelled']),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Expiry tracker updated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/ExpiryTrackerData'),
            ])),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateExpiryTrackerRequest $request, ExpiryTracker $expiryTracker): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $expiryTracker->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        if (!$user->hasRole('super-admin') && $expiryTracker->module && !$user->canOnModule($expiryTracker->module, 'update')) {
            abort(403, 'Forbidden');
        }
        $this->checkOptimisticLock($expiryTracker, $request);
        $validated = $request->validated();
        $entry = $this->expiryTrackerService->update($expiryTracker, $validated);

        return $this->success($entry, 'Renewal updated');
    }

    #[OA\Delete(
        path: '/expiry-trackers/{id}',
        summary: 'Soft-delete a renewal entry',
        security: [['sanctum' => []]],
        tags: ['Renewals'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Expiry tracker deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function destroy(Request $request, ExpiryTracker $expiryTracker): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $expiryTracker->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        if (!$user->hasRole('super-admin') && $expiryTracker->module && !$user->canOnModule($expiryTracker->module, 'delete')) {
            abort(403, 'Forbidden');
        }
        $this->expiryTrackerService->delete($expiryTracker);

        return $this->message('Renewal deleted');
    }
}
