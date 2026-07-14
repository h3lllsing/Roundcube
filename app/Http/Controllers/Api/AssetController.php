<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\Module;
use App\Services\AssetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function __construct(private readonly AssetService $assetService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->only(['category_id', 'status', 'location_id', 'assigned_to', 'condition', 'department', 'search', 'per_page', 'sort_by', 'sort_order']);
        if ($user->hasRole('super-admin') && $request->boolean('with_trashed')) {
            $filters['with_trashed'] = true;
        }
        if (! $user->hasRole('super-admin')) {
            $ids = $user->getAccessibleModuleIds('read');
            $filters['accessible_module_ids'] = $ids ?: [0];
        }

        return response()->json($this->assetService->list($filters));
    }

    public function store(StoreAssetRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $user = $request->user();
        if (!$user->hasRole('super-admin')) {
            $moduleId = $validated['module_id'] ?? null;
            abort_unless($moduleId && $user->canOnModule(Module::find($moduleId), 'create'), 403, 'Forbidden');
        }

        return $this->created($this->assetService->create($validated), 'Asset created');
    }

    public function show(Request $request, Asset $asset): JsonResponse
    {
        $asset->load(['category', 'type', 'location', 'assignee', 'user', 'module.feature', 'vaultEntry']);
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $asset->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }

        return $this->success($asset);
    }

    public function update(UpdateAssetRequest $request, Asset $asset): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super-admin') && $asset->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        if (!$user->hasRole('super-admin') && $asset->module && !$user->canOnModule($asset->module, 'update')) {
            abort(403, 'Forbidden');
        }
        $this->checkOptimisticLock($asset, $request);

        return $this->success($this->assetService->update($asset, $request->validated()), 'Asset updated');
    }

    public function destroy(Request $request, Asset $asset): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole('super-admin'), 403);
        $this->assetService->delete($asset);

        return $this->message('Asset deleted');
    }
}
