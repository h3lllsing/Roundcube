<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoipRequest;
use App\Http\Requests\UpdateVoipRequest;
use App\Models\Voip;
use App\Services\VoipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class VoipController extends Controller
{
    public function __construct(private readonly VoipService $voipService) {}

    #[OA\Get(
        path: '/voip',
        summary: 'List VoIP entries',
        security: [['sanctum' => []]],
        tags: ['VoIP'],
        parameters: [
            new OA\Parameter(name: 'module_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'expired', 'cancelled'])),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['name', 'type', 'cost', 'status', 'expiry_date', 'created_at'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'with_trashed', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of VoIP entries', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/VoipData')),
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

        return response()->json($this->voipService->list($filters));
    }

    #[OA\Post(
        path: '/voip',
        summary: 'Create a VoIP entry',
        security: [['sanctum' => []]],
        tags: ['VoIP'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'extensions', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
                new OA\Property(property: 'service_provider_id', type: 'integer', nullable: true),
                new OA\Property(property: 'phone_number', type: 'string', nullable: true),
                new OA\Property(property: 'type', type: 'string', default: 'sip', enum: ['sip', 'trunk', 'phone']),
                new OA\Property(property: 'direction', type: 'string', nullable: true, enum: ['inbound', 'outbound', 'both']),
                new OA\Property(property: 'username', type: 'string', nullable: true),
                new OA\Property(property: 'password', type: 'string', nullable: true),
                new OA\Property(property: 'extension_password', type: 'string', nullable: true),
                new OA\Property(property: 'dashboard_url', type: 'string', nullable: true),
                new OA\Property(property: 'server_ip', type: 'string', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'status', type: 'string', default: 'active', enum: ['active', 'inactive', 'expired', 'suspended', 'pending_transfer', 'cancelled']),
                new OA\Property(property: 'number_status', type: 'string', nullable: true),
                new OA\Property(property: 'outbound_code', type: 'string', nullable: true),
                new OA\Property(property: 'team_details', type: 'string', nullable: true),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'VoIP created', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/VoipData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreVoipRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $validated['extensions'] = ! empty($validated['extension']) ? [$validated['extension']] : [];
        unset($validated['extension']);
        $user = $request->user();
        if (!$user->hasRole('super-admin')) {
            $moduleId = $validated['module_id'] ?? null;
            abort_unless($moduleId && $user->canOnModule(\App\Models\Module::find($moduleId), 'create'), 403, 'Forbidden');
        }

        return $this->created($this->voipService->create($validated), 'VoIP created');
    }

    #[OA\Get(
        path: '/voip/{id}',
        summary: 'Get a VoIP entry',
        security: [['sanctum' => []]],
        tags: ['VoIP'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'VoIP details', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/VoipData'),
            ])),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Request $request, Voip $voip): JsonResponse
    {
        $voip->load('module.feature', 'user');
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $voip->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }

        return $this->success($voip);
    }

    #[OA\Put(
        path: '/voip/{id}',
        summary: 'Update a VoIP entry',
        security: [['sanctum' => []]],
        tags: ['VoIP'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'extensions', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
                new OA\Property(property: 'service_provider_id', type: 'integer', nullable: true),
                new OA\Property(property: 'phone_number', type: 'string', nullable: true),
                new OA\Property(property: 'type', type: 'string', enum: ['sip', 'trunk', 'phone']),
                new OA\Property(property: 'direction', type: 'string', nullable: true, enum: ['inbound', 'outbound', 'both']),
                new OA\Property(property: 'username', type: 'string', nullable: true),
                new OA\Property(property: 'password', type: 'string', nullable: true),
                new OA\Property(property: 'extension_password', type: 'string', nullable: true),
                new OA\Property(property: 'dashboard_url', type: 'string', nullable: true),
                new OA\Property(property: 'server_ip', type: 'string', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'expired', 'suspended', 'pending_transfer', 'cancelled']),
                new OA\Property(property: 'number_status', type: 'string', nullable: true),
                new OA\Property(property: 'outbound_code', type: 'string', nullable: true),
                new OA\Property(property: 'team_details', type: 'string', nullable: true),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'VoIP updated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/VoipData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateVoipRequest $request, Voip $voip): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $voip->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        if (!$user->hasRole('super-admin') && $voip->module && !$user->canOnModule($voip->module, 'update')) {
            abort(403, 'Forbidden');
        }
        $this->checkOptimisticLock($voip, $request);

        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }
        if (empty($data['extension_password'])) {
            unset($data['extension_password']);
        }
        if (array_key_exists('extension', $data)) {
            $data['extensions'] = ! empty($data['extension']) ? [$data['extension']] : [];
            unset($data['extension']);
        }

        return $this->success($this->voipService->update($voip, $data), 'VoIP updated');
    }

    #[OA\Delete(
        path: '/voip/{id}',
        summary: 'Soft-delete a VoIP entry',
        security: [['sanctum' => []]],
        tags: ['VoIP'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'VoIP deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function destroy(Request $request, Voip $voip): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $voip->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        if (!$user->hasRole('super-admin') && $voip->module && !$user->canOnModule($voip->module, 'delete')) {
            abort(403, 'Forbidden');
        }
        $this->voipService->delete($voip);

        return $this->message('VoIP deleted');
    }
}
