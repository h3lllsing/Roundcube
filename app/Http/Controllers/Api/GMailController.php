<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GMail;
use App\Models\Module;
use App\Helpers\ModuleCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GMailController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = GMail::with('module');
        if (!$user->hasRole('super-admin')) {
            $ids = $user->getAccessibleModuleIds('read');
            $query->whereIn('module_id', $ids ?: [0]);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('user_name', 'like', "%{$s}%")
                  ->orWhere('pseudo', 'like', "%{$s}%")
                  ->orWhere('emails_address', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $records = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json($records);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'nullable|string|max:255',
            'user_name' => 'nullable|string|max:255',
            'pseudo' => 'nullable|string|max:255',
            'emails_address' => 'nullable|string|max:255',
            'password' => 'nullable|string',
            'security_number' => 'nullable|string|max:255',
            'security_number_person' => 'nullable|string|max:255',
            'recovery_email' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'assigned' => 'nullable|string|max:255',
            'user_remarks' => 'nullable|string',
            'comments' => 'nullable|string',
        ]);
        $user = $request->user();
        if (!$user->hasRole('super-admin')) {
            $moduleId = $validated['module_id'] ?? null;
            abort_unless($moduleId && $user->canOnModule(Module::find($moduleId), 'create'), 403, 'Forbidden');
        }
        $validated['user_id'] = $user->id;
        $gMail = GMail::create($validated);

        return response()->json(['message' => 'G-Mail created', 'data' => $gMail], 201);
    }

    public function show(Request $request, GMail $gMail): JsonResponse
    {
        $gMail->load('module', 'user');
        $user = $request->user();
        if (!$user->hasRole('super-admin') && $gMail->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }

        return $this->success($gMail);
    }

    public function update(Request $request, GMail $gMail): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole('super-admin') && $gMail->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
        if (!$user->hasRole('super-admin') && $gMail->module && !$user->canOnModule($gMail->module, 'update')) {
            abort(403, 'Forbidden');
        }
        $this->checkOptimisticLock($gMail, $request);
        $validated = $request->validate(['updated_at' => 'required|date',
            'status' => 'nullable|string|max:255',
            'user_name' => 'nullable|string|max:255',
            'pseudo' => 'nullable|string|max:255',
            'emails_address' => 'nullable|string|max:255',
            'password' => 'nullable|string',
            'security_number' => 'nullable|string|max:255',
            'security_number_person' => 'nullable|string|max:255',
            'recovery_email' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'assigned' => 'nullable|string|max:255',
            'user_remarks' => 'nullable|string',
            'comments' => 'nullable|string',
        ]);
        if (empty($validated['password'])) {
            unset($validated['password']);
        }
        unset($validated['module_id']);
        $gMail->update($validated);

        return response()->json(['message' => 'G-Mail updated', 'data' => $gMail]);
    }

    public function destroy(Request $request, GMail $gMail): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole('super-admin'), 403);
        $gMail->delete();

        return response()->json(['message' => 'G-Mail deleted']);
    }
}
