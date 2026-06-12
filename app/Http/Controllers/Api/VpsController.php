<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVpsRequest;
use App\Http\Requests\UpdateVpsRequest;
use App\Models\Vps;
use App\Services\VpsService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class VpsController extends Controller
{
    public function __construct(private readonly VpsService $vpsService) {}

    #[OA\Get(
        path: '/vps',
        summary: 'List VPS entries',
        security: [['sanctum' => []]],
        tags: ['VPS'],
        parameters: [
            new OA\Parameter(name: 'module_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'expired', 'cancelled'])),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['name', 'provider', 'plan', 'ip_address', 'os', 'cost', 'status', 'expiry_date', 'created_at'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'with_trashed', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of VPS', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/VpsData')),
            ])),
        ]
    )]
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $filters = $request->only(['module_id', 'search', 'status', 'per_page', 'sort_by', 'sort_order']);
        if ($user->hasRole('super-admin') && $request->boolean('with_trashed')) $filters['with_trashed'] = true;
        if (!$user->hasRole('super-admin')) $filters['user_id'] = $user->id;
        return response()->json($this->vpsService->list($filters));
    }

    #[OA\Post(
        path: '/vps',
        summary: 'Create a VPS entry',
        security: [['sanctum' => []]],
        tags: ['VPS'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'provider', type: 'string', nullable: true),
                new OA\Property(property: 'plan', type: 'string', nullable: true),
                new OA\Property(property: 'ip_address', type: 'string', nullable: true),
                new OA\Property(property: 'os', type: 'string', nullable: true),
                new OA\Property(property: 'ram_mb', type: 'integer', nullable: true),
                new OA\Property(property: 'disk_gb', type: 'integer', nullable: true),
                new OA\Property(property: 'cpu_cores', type: 'integer', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'status', type: 'string', default: 'active', enum: ['active', 'expired', 'cancelled']),
                new OA\Property(property: 'notes', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'VPS created', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/VpsData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreVpsRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        return $this->created($this->vpsService->create($validated), 'VPS created');
    }

    #[OA\Get(
        path: '/vps/{id}',
        summary: 'Get a VPS entry',
        security: [['sanctum' => []]],
        tags: ['VPS'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'VPS details', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/VpsData'),
            ])),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Request $request, Vps $vps): \Illuminate\Http\JsonResponse
    {
        $vps->load('module.feature', 'user');
        $user = $request->user();
        if (!$user->hasRole('super-admin') && $vps->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        return $this->success($vps);
    }

    #[OA\Put(
        path: '/vps/{id}',
        summary: 'Update a VPS entry',
        security: [['sanctum' => []]],
        tags: ['VPS'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'provider', type: 'string', nullable: true),
                new OA\Property(property: 'plan', type: 'string', nullable: true),
                new OA\Property(property: 'ip_address', type: 'string', nullable: true),
                new OA\Property(property: 'os', type: 'string', nullable: true),
                new OA\Property(property: 'ram_mb', type: 'integer', nullable: true),
                new OA\Property(property: 'disk_gb', type: 'integer', nullable: true),
                new OA\Property(property: 'cpu_cores', type: 'integer', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'expired', 'cancelled']),
                new OA\Property(property: 'notes', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'VPS updated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/VpsData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateVpsRequest $request, Vps $vps): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin') && $vps->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        return $this->success($this->vpsService->update($vps, $request->validated()), 'VPS updated');
    }

    #[OA\Delete(
        path: '/vps/{id}',
        summary: 'Soft-delete a VPS entry',
        security: [['sanctum' => []]],
        tags: ['VPS'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'VPS deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function destroy(Request $request, Vps $vps): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin') && $vps->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        $this->vpsService->delete($vps);
        return $this->message('VPS deleted');
    }
}
