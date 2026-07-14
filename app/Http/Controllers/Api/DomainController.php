<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDomainRequest;
use App\Http\Requests\UpdateDomainRequest;
use App\Models\Domain;
use App\Services\DomainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DomainController extends Controller
{
    public function __construct(private readonly DomainService $domainService) {}

    #[OA\Get(
        path: '/domains',
        summary: 'List domains',
        security: [['sanctum' => []]],
        tags: ['Domains'],
        parameters: [
            new OA\Parameter(name: 'module_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'expired', 'pending_transfer', 'cancelled'])),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['name', 'registrar', 'expiry_date', 'cost', 'status', 'created_at'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'with_trashed', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of domains', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/DomainData')),
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

        return response()->json($this->domainService->list($filters));
    }

    #[OA\Post(
        path: '/domains',
        summary: 'Create a domain entry',
        security: [['sanctum' => []]],
        tags: ['Domains'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'registrar', type: 'string', nullable: true),
                new OA\Property(property: 'registration_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'auto_renew', type: 'boolean', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'status', type: 'string', default: 'active', enum: ['active', 'expired', 'pending_transfer', 'cancelled']),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Domain created', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/DomainData'),
                new OA\Property(property: 'message', type: 'string'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function store(StoreDomainRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $user = $request->user();
        if (!$user->hasRole('super-admin')) {
            $moduleId = $validated['module_id'] ?? null;
            abort_unless($moduleId && $user->canOnModule(\App\Models\Module::find($moduleId), 'create'), 403, 'Forbidden');
        }

        return $this->created($this->domainService->create($validated), 'Domain created');
    }

    #[OA\Get(
        path: '/domains/{id}',
        summary: 'Get a domain entry',
        security: [['sanctum' => []]],
        tags: ['Domains'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Domain details', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/DomainData'),
            ])),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Request $request, Domain $domain): JsonResponse
    {
        $domain->load('module.feature', 'user');
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $domain->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }

        return $this->success($domain);
    }

    #[OA\Put(
        path: '/domains/{id}',
        summary: 'Update a domain entry',
        security: [['sanctum' => []]],
        tags: ['Domains'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'registrar', type: 'string', nullable: true),
                new OA\Property(property: 'registration_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'auto_renew', type: 'boolean', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'expired', 'pending_transfer', 'cancelled']),
                new OA\Property(property: 'dns_servers', type: 'string', nullable: true),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Domain updated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/DomainData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateDomainRequest $request, Domain $domain): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $domain->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        if (!$user->hasRole('super-admin') && $domain->module && !$user->canOnModule($domain->module, 'update')) {
            abort(403, 'Forbidden');
        }
        $this->checkOptimisticLock($domain, $request);

        return $this->success($this->domainService->update($domain, $request->validated()), 'Domain updated');
    }

    #[OA\Delete(
        path: '/domains/{id}',
        summary: 'Soft-delete a domain entry',
        security: [['sanctum' => []]],
        tags: ['Domains'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Domain deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function destroy(Request $request, Domain $domain): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole('super-admin'), 403);
        $this->domainService->delete($domain);

        return $this->message('Domain deleted');
    }
}
