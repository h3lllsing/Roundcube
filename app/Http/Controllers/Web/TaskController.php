<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Module;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    private function userOwnedFilter(): void
    {
        $user = Auth::user();

        if ($user->hasRole('super-admin')) {
            return;
        }

        if ($user->hasRole('admin')) {
            $accessibleIds = $user->getAccessibleModuleIds('read');
            if (! empty($accessibleIds)) {
                Task::addGlobalScope('adminScope', fn ($q) => $q->where(function ($q) use ($accessibleIds, $user) {
                    $q->whereIn('module_id', $accessibleIds)
                      ->orWhereHas('assignees', fn ($a) => $a->where('user_id', $user->id));
                }));

                return;
            }
        }

        Task::addGlobalScope('ownership', fn ($q) => $q->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhereHas('assignees', fn ($a) => $a->where('user_id', $user->id));
        }));
    }

    public function index(Request $request): View
    {
        $this->userOwnedFilter();
        $query = Task::with(['module.feature', 'assignees', 'creator']);

        if ($request->boolean('trashed')) {
            $query->onlyTrashed();
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }
        if ($request->filled('module_id')) {
            $query->where('module_id', $request->module_id);
        }
        if ($request->filled('assigned_to')) {
            $query->whereHas('assignees', fn ($q) => $q->where('user_id', $request->assigned_to));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->boolean('my_tasks')) {
            $query->whereHas('assignees', fn ($q) => $q->where('user_id', Auth::id()));
        }

        $sortBy = in_array($request->sort_by, ['title', 'status', 'priority', 'due_date', 'created_at', 'updated_at']) ? $request->sort_by : 'created_at';
        $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';

        $tasks = $query->select(['id', 'module_id', 'title', 'status', 'priority', 'due_date', 'created_at'])->orderBy($sortBy, $sortOrder)->paginate(20);

        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('tasks.index', compact('tasks', 'modules', 'users'));
    }

    public function myTasks(Request $request): View
    {
        $this->userOwnedFilter();
        $query = Task::with(['module.feature', 'assignees', 'creator'])
            ->whereHas('assignees', fn ($q) => $q->where('user_id', Auth::id()));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }
        if ($request->filled('module_id')) {
            $query->where('module_id', $request->module_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sortBy = in_array($request->sort_by, ['title', 'status', 'priority', 'due_date', 'created_at', 'updated_at']) ? $request->sort_by : 'created_at';
        $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';

        $tasks = $query->select(['id', 'module_id', 'title', 'status', 'priority', 'due_date', 'created_at'])->orderBy($sortBy, $sortOrder)->paginate(20);

        $modules = Module::orderBy('name')->pluck('name', 'id');

        return view('tasks.my-tasks', compact('tasks', 'modules'));
    }

    public function myTaskCounts(): JsonResponse
    {
        $this->userOwnedFilter();
        $userId = Auth::id();
        $counts = Task::whereHas('assignees', fn ($q) => $q->where('user_id', $userId))
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'total' => array_sum($counts->toArray()),
            'pending' => (int) ($counts['pending'] ?? 0),
            'in_progress' => (int) ($counts['in_progress'] ?? 0),
            'completed' => (int) ($counts['completed'] ?? 0),
            'cancelled' => (int) ($counts['cancelled'] ?? 0),
        ]);
    }

    public function kanban(Request $request): View
    {
        $this->userOwnedFilter();
        $query = Task::with(['module.feature', 'assignees', 'creator']);

        if ($request->boolean('my_tasks')) {
            $query->whereHas('assignees', fn ($q) => $q->where('user_id', Auth::id()));
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->select(['id', 'module_id', 'title', 'status', 'priority', 'due_date'])->limit(500)->get();

        $columns = [
            'pending' => $tasks->where('status', 'pending'),
            'in_progress' => $tasks->where('status', 'in_progress'),
            'completed' => $tasks->where('status', 'completed'),
            'cancelled' => $tasks->where('status', 'cancelled'),
        ];

        return view('tasks.kanban', compact('columns'));
    }

    public function create(): View
    {
        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('tasks.create', compact('modules', 'users'));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();
        $user = Auth::user();
        if (!$user->hasRole('super-admin')) {
            $moduleId = $validated['module_id'] ?? null;
            abort_unless($moduleId && $user->canOnModule(Module::find($moduleId), 'create'), 403, 'Forbidden');
        }

        $this->taskService->create($validated);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(int $id): View
    {
        $this->userOwnedFilter();
        $task = Task::with(['module.feature', 'assignees', 'creator', 'attachments'])->findOrFail($id);

        $activities = Activity::where('subject_type', Task::class)
            ->where('subject_id', $task->id)
            ->with('causer')
            ->latest()
            ->get();

        return view('tasks.show', compact('task', 'activities'));
    }

    public function edit(int $id): View
    {
        $this->userOwnedFilter();
        $task = Task::with(['module.feature', 'assignees', 'creator'])->findOrFail($id);
        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('tasks.edit', compact('task', 'modules', 'users'));
    }

    public function update(UpdateTaskRequest $request, int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $task = Task::findOrFail($id);
        $user = Auth::user();
        if (!$user->hasRole('super-admin') && $task->module && !$user->canOnModule($task->module, 'update')) {
            abort(403, 'Forbidden');
        }
        $this->checkOptimisticLock($task, $request);

        $validated = $request->validated();
        $validated['updated_by'] = Auth::id();

        $this->taskService->update($task, $validated);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $task = Task::findOrFail($id);
        $user = Auth::user();
        if (!$user->hasRole('super-admin') && $task->module && !$user->canOnModule($task->module, 'update')) {
            abort(403, 'Forbidden');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
        ]);

        $task->update([
            'status' => $validated['status'],
            'updated_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Task status updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $task = Task::findOrFail($id);
        $user = Auth::user();
        abort_unless($user->hasRole('super-admin'), 403);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $this->userOwnedFilter();
        $task = Task::withTrashed()->findOrFail($id);
        $task->restore();

        return redirect()->route('tasks.index')->with('success', 'Task restored successfully.');
    }
}
