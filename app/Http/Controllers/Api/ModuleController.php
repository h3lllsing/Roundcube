<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreModuleRequest;
use App\Http\Requests\UpdateModuleRequest;
use App\Http\Resources\ModuleResource;
use App\Models\Feature;
use App\Models\Module;
use App\Services\ModuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class ModuleController extends Controller
{
    public function __construct(
        private readonly ModuleService $moduleService
    ) {}

    #[OA\Get(
        path: '/features/{feature}/modules',
        summary: 'List modules under a feature',
        security: [['sanctum' => []]],
        tags: ['Modules'],
        parameters: [
            new OA\Parameter(name: 'feature', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'is_active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['name', 'created_at', 'updated_at'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of modules', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ModuleData')),
            ])),
        ]
    )]
    public function index(Request $request, Feature $feature): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $version = Cache::get('features:version', 0);
        $filters = $request->only(['is_active', 'search', 'per_page', 'sort_by', 'sort_order']);

        if ($request->user()->hasRole('super-admin') && $request->boolean('with_trashed')) {
            $filters['with_trashed'] = true;
        }
        $cacheKey = 'modules:list:v' . $version . ':' . $feature->id . ':' . md5(json_encode($filters) ?: '');
        $modules = Cache::remember($cacheKey, 60, fn() => $this->moduleService->listForFeature($feature, $filters));
        return ModuleResource::collection($modules);
    }

    #[OA\Post(
        path: '/features/{feature}/modules',
        summary: 'Create a module under a feature',
        security: [['sanctum' => []]],
        tags: ['Modules'],
        parameters: [
            new OA\Parameter(name: 'feature', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'slug', type: 'string'),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'is_active', type: 'boolean', default: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Module created', content: new OA\JsonContent(ref: '#/components/schemas/ModuleData')),
        ]
    )]
    public function store(StoreModuleRequest $request, Feature $feature): ModuleResource
    {
        $module = $this->moduleService->create($feature, $request->validated());
        return new ModuleResource($module);
    }

    #[OA\Get(
        path: '/modules/{id}',
        summary: 'Get a single module with feature and permissions',
        security: [['sanctum' => []]],
        tags: ['Modules'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Module details', content: new OA\JsonContent(ref: '#/components/schemas/ModuleData')),
        ]
    )]
    public function show(Module $module): ModuleResource
    {
        $module->loadMissing('feature', 'rolePermissions.role');
        return new ModuleResource($module);
    }

    #[OA\Put(
        path: '/modules/{id}',
        summary: 'Update a module',
        security: [['sanctum' => []]],
        tags: ['Modules'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'slug', type: 'string'),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'is_active', type: 'boolean'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Module updated', content: new OA\JsonContent(ref: '#/components/schemas/ModuleData')),
        ]
    )]
    public function update(UpdateModuleRequest $request, Module $module): ModuleResource
    {
        $module = $this->moduleService->update($module, $request->validated());
        return new ModuleResource($module);
    }

    #[OA\Delete(
        path: '/modules/{id}',
        summary: 'Soft delete a module',
        security: [['sanctum' => []]],
        tags: ['Modules'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Module deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function destroy(Module $module): \Illuminate\Http\JsonResponse
    {
        $this->moduleService->delete($module);
        return $this->message('Module deleted');
    }
}
