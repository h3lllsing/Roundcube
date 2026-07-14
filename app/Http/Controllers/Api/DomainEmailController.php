<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDomainEmailRequest;
use App\Http\Requests\UpdateDomainEmailRequest;
use App\Models\DomainEmail;
use App\Services\DomainEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DomainEmailController extends Controller
{
    public function __construct(private readonly DomainEmailService $domainEmailService) {}

    #[OA\Get(
        path: '/domain-emails',
        summary: 'List domain email entries',
        security: [['sanctum' => []]],
        tags: ['Domain Emails'],
        parameters: [
            new OA\Parameter(name: 'module_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'domain_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'expired', 'cancelled'])),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['email', 'cost', 'status', 'expiry_date', 'created_at'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'with_trashed', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of domain emails', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/DomainEmailData')),
            ])),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->only(['module_id', 'domain_id', 'search', 'status', 'per_page', 'sort_by', 'sort_order']);
        if ($user->hasRole('super-admin') && $request->boolean('with_trashed')) {
            $filters['with_trashed'] = true;
        }
        if (! $user->hasRole('super-admin')) {
            $ids = $user->getAccessibleModuleIds('read');
            $filters['accessible_module_ids'] = $ids ?: [0];
        }

        return response()->json($this->domainEmailService->list($filters));
    }

    #[OA\Post(
        path: '/domain-emails',
        summary: 'Create a domain email entry',
        security: [['sanctum' => []]],
        tags: ['Domain Emails'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'service_provider_id', type: 'integer', nullable: true),
                new OA\Property(property: 'domain_id', type: 'integer', nullable: true),
                new OA\Property(property: 'storage_mb', type: 'integer', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'status', type: 'string', default: 'active', enum: ['active', 'expired', 'cancelled']),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Domain email created', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/DomainEmailData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreDomainEmailRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $user = $request->user();
        if (!$user->hasRole('super-admin')) {
            $moduleId = $validated['module_id'] ?? null;
            abort_unless($moduleId && $user->canOnModule(\App\Models\Module::find($moduleId), 'create'), 403, 'Forbidden');
        }

        return $this->created($this->domainEmailService->create($validated), 'Domain email created');
    }

    #[OA\Get(
        path: '/domain-emails/{id}',
        summary: 'Get a domain email entry',
        security: [['sanctum' => []]],
        tags: ['Domain Emails'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Domain email details', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/DomainEmailData'),
            ])),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Request $request, DomainEmail $domainEmail): JsonResponse
    {
        $domainEmail->load('domain', 'module.feature', 'user');
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $domainEmail->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }

        return $this->success($domainEmail);
    }

    #[OA\Put(
        path: '/domain-emails/{id}',
        summary: 'Update a domain email entry',
        security: [['sanctum' => []]],
        tags: ['Domain Emails'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'service_provider_id', type: 'integer', nullable: true),
                new OA\Property(property: 'domain_id', type: 'integer', nullable: true),
                new OA\Property(property: 'storage_mb', type: 'integer', nullable: true),
                new OA\Property(property: 'cost', type: 'number', format: 'float', nullable: true),
                new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'expired', 'cancelled']),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Domain email updated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/DomainEmailData'),
            ])),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateDomainEmailRequest $request, DomainEmail $domainEmail): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $domainEmail->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        if (!$user->hasRole('super-admin') && $domainEmail->module && !$user->canOnModule($domainEmail->module, 'update')) {
            abort(403, 'Forbidden');
        }
        $this->checkOptimisticLock($domainEmail, $request);

        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $this->success($this->domainEmailService->update($domainEmail, $data), 'Domain email updated');
    }

    #[OA\Delete(
        path: '/domain-emails/{id}',
        summary: 'Soft-delete a domain email entry',
        security: [['sanctum' => []]],
        tags: ['Domain Emails'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Domain email deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function destroy(Request $request, DomainEmail $domainEmail): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole('super-admin'), 403);
        $this->domainEmailService->delete($domainEmail);

        return $this->message('Domain email deleted');
    }
}
