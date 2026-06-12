<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Module;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $query = Task::with(['module.feature', 'assignees']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $tasks = $query->latest()->paginate(20);

        return view('tasks.index', compact('tasks'));
    }

    public function create(): View
    {
        $modules = Module::orderBy('name')->pluck('name', 'id');

        return view('tasks.create', compact('modules'));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();

        Task::create($validated);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(int $id): View
    {
        $task = Task::with(['module.feature', 'assignees', 'creator'])->findOrFail($id);

        return view('tasks.show', compact('task'));
    }

    public function edit(int $id): View
    {
        $task = Task::findOrFail($id);
        $modules = Module::orderBy('name')->pluck('name', 'id');

        return view('tasks.edit', compact('task', 'modules'));
    }

    public function update(UpdateTaskRequest $request, int $id): RedirectResponse
    {
        $task = Task::findOrFail($id);

        $validated = $request->validated();
        $validated['updated_by'] = Auth::id();

        $task->update($validated);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}
