<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeatureRequest;
use App\Http\Requests\UpdateFeatureRequest;
use App\Http\Resources\FeatureResource;
use App\Models\Feature;
use App\Models\Module;
use App\Services\FeatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class FeatureController extends Controller
{
    public function __construct(
        private readonly FeatureService $featureService
    ) {}

    #[OA\Get(
        path: '/features',
        summary: 'List all features',
        security: [['sanctum' => []]],
        tags: ['Features'],
        parameters: [
            new OA\Parameter(name: 'is_active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['name', 'created_at', 'updated_at'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'with_trashed', in: 'query', required: false, schema: new OA\Schema(type: 'boolean'), description: 'Super-admin only: include soft-deleted'),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of features', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/FeatureData')),
            ])),
        ]
    )]
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $user = $request->user();
        $filters = $request->only(['is_active', 'search', 'per_page', 'sort_by', 'sort_order']);

        if ($user->hasRole('super-admin') && $request->boolean('with_trashed')) {
            $filters['with_trashed'] = true;
        }

        if (!$user->hasRole('super-admin')) {
            $filters['accessible_module_ids'] = Module::whereHas('rolePermissions', function ($q) use ($user) {
                $q->whereIn('role_id', $user->roles()->pluck('roles.id'))->where('can_read', true);
            })->pluck('id');
        }

        $version = Cache::get('features:version', 0);
        $cacheKey = 'features:list:v' . $version . ':' . $user->id . ':' . md5(json_encode($filters) ?: '');
        $features = Cache::remember($cacheKey, 60, fn() => $this->featureService->list($filters));
        return FeatureResource::collection($features);
    }

    #[OA\Post(
        path: '/features',
        summary: 'Create a new feature',
        security: [['sanctum' => []]],
        tags: ['Features'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'slug', type: 'string'),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'icon', type: 'string', nullable: true),
                new OA\Property(property: 'is_active', type: 'boolean', default: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Feature created', content: new OA\JsonContent(ref: '#/components/schemas/FeatureData')),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreFeatureRequest $request): FeatureResource
    {
        $feature = $this->featureService->create($request->validated());
        return new FeatureResource($feature);
    }

    #[OA\Get(
        path: '/features/{id}',
        summary: 'Get a single feature with modules',
        security: [['sanctum' => []]],
        tags: ['Features'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Feature details', content: new OA\JsonContent(ref: '#/components/schemas/FeatureData')),
            new OA\Response(response: 404, description: 'Feature not found'),
        ]
    )]
    public function show(Feature $feature): FeatureResource
    {
        $feature->loadMissing('modules');
        return new FeatureResource($feature);
    }

    #[OA\Put(
        path: '/features/{id}',
        summary: 'Update a feature',
        security: [['sanctum' => []]],
        tags: ['Features'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'slug', type: 'string'),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'icon', type: 'string', nullable: true),
                new OA\Property(property: 'is_active', type: 'boolean'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Feature updated', content: new OA\JsonContent(ref: '#/components/schemas/FeatureData')),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateFeatureRequest $request, Feature $feature): FeatureResource
    {
        $feature = $this->featureService->update($feature, $request->validated());
        return new FeatureResource($feature);
    }

    #[OA\Delete(
        path: '/features/{id}',
        summary: 'Soft delete a feature',
        security: [['sanctum' => []]],
        tags: ['Features'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Feature deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 404, description: 'Feature not found'),
        ]
    )]
    public function destroy(Feature $feature): \Illuminate\Http\JsonResponse
    {
        $this->featureService->delete($feature);
        return $this->message('Feature deleted');
    }
}
