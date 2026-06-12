<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOtherServiceRequest;
use App\Http\Requests\UpdateOtherServiceRequest;
use App\Models\OtherService;
use App\Services\OtherServiceService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class OtherServiceController extends Controller
{
    public function __construct(private readonly OtherServiceService $otherServiceService) {}

    #[OA\Get(
        path: '/other-services',
        summary: 'List other service entries',
        security: [['sanctum' => []]],
        tags: ['Other Services'],
        parameters: [
            new OA\Parameter(name: 'module_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'expired', 'cancelled'])),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['name', 'service_type', 'provider', 'cost', 'status', 'expiry_date', 'created_at'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'with_trashed', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of other services', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/OtherServiceData')),
            ])),
        ]
    )]
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $filters = $request->only(['module_id', 'search', 'status', 'per_page', 'sort_by', 'sort_order']);
        if ($user->hasRole('super-admin') && $request->boolean('with_trashed')) $filters['with_trashed'] = true;
        if (!$user->hasRole('super-admin')) $filters['user_id'] = $user->id;
        return response()->json($this->otherServiceService->list($filters));
    }

    #[OA\Post(
        path: '/other-services',
        summary: 'Create an other service entry',
        security: [['sanctum' => []]],
        tags: ['Other Services'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'service_type', type: 'string', enum: ['saas', 'api', 'monitoring', 'analytics', 'cdn', 'ssl', 'other']),
                new OA\Property(property: 'provider', type: 'string', nullable: true),
                new OA\Property(property: 'website', type: 'string', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'status', type: 'string', default: 'active', enum: ['active', 'expired', 'cancelled']),
                new OA\Property(property: 'notes', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Other service created', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/OtherServiceData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreOtherServiceRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        return $this->created($this->otherServiceService->create($validated), 'Other service created');
    }

    #[OA\Get(
        path: '/other-services/{id}',
        summary: 'Get an other service entry',
        security: [['sanctum' => []]],
        tags: ['Other Services'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Other service details', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/OtherServiceData'),
            ])),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Request $request, OtherService $otherService): \Illuminate\Http\JsonResponse
    {
        $otherService->load('module.feature', 'user');
        $user = $request->user();
        if (!$user->hasRole('super-admin') && $otherService->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        return $this->success($otherService);
    }

    #[OA\Put(
        path: '/other-services/{id}',
        summary: 'Update an other service entry',
        security: [['sanctum' => []]],
        tags: ['Other Services'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'service_type', type: 'string', enum: ['saas', 'api', 'monitoring', 'analytics', 'cdn', 'ssl', 'other']),
                new OA\Property(property: 'provider', type: 'string', nullable: true),
                new OA\Property(property: 'website', type: 'string', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'expired', 'cancelled']),
                new OA\Property(property: 'notes', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Other service updated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/OtherServiceData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateOtherServiceRequest $request, OtherService $otherService): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin') && $otherService->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        return $this->success($this->otherServiceService->update($otherService, $request->validated()), 'Other service updated');
    }

    #[OA\Delete(
        path: '/other-services/{id}',
        summary: 'Soft-delete an other service entry',
        security: [['sanctum' => []]],
        tags: ['Other Services'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Other service deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function destroy(Request $request, OtherService $otherService): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin') && $otherService->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        $this->otherServiceService->delete($otherService);
        return $this->message('Other service deleted');
    }
}
