<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Feature;
use App\Models\Module;
use App\Models\Note;
use App\Services\NoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function __construct(
        private readonly NoteService $noteService
    ) {}

    public function index(Request $request): View
    {
        $this->noteService->applyUserScope(Auth::user());

        $filters = $request->only(['search', 'notable_type']);
        $notes = $this->noteService->listAll($filters);
        $notableTypes = $this->noteService->getDistinctTypes();

        $user = Auth::user();
        $noteModule = \App\Helpers\ModuleCache::findBySlug('notes');
        $canExport = $user->hasRole('super-admin') || ($noteModule && $user->canOnModule($noteModule, 'export'));

        return view('notes.index', compact('notes', 'notableTypes', 'canExport'));
    }

    public function create(): View
    {
        $features = Feature::orderBy('name')->pluck('name', 'id');
        $modules = Module::orderBy('name')->pluck('name', 'id');

        return view('notes.create', compact('features', 'modules'));
    }

    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        $user = Auth::user();
        if (! $user->hasRole('super-admin') && ($validated['notable_type'] ?? null) === 'App\Models\Module') {
            $module = Module::findOrFail($validated['notable_id']);
            abort_unless($module && $user->canOnModule($module, 'create'), 403);
        }

        Note::create($validated);

        return redirect()->route('notes.index')->with('success', 'Note created successfully.');
    }

    public function show(int $id): View
    {
        $this->noteService->applyUserScope(Auth::user());
        $note = Note::with('user', 'attachments')->findOrFail($id);

        return view('notes.show', compact('note'));
    }

    public function edit(int $id): View
    {
        $this->noteService->applyUserScope(Auth::user());
        $note = Note::findOrFail($id);
        $features = Feature::orderBy('name')->pluck('name', 'id');
        $modules = Module::orderBy('name')->pluck('name', 'id');

        return view('notes.edit', compact('note', 'features', 'modules'));
    }

    public function update(UpdateNoteRequest $request, int $id): RedirectResponse
    {
        $this->noteService->applyUserScope(Auth::user());
        $note = Note::with('notable')->findOrFail($id);
        $this->authorizeNoteAccess($note, 'update');
        $this->checkOptimisticLock($note, $request);

        $note->update($request->validated());

        return redirect()->route('notes.index')->with('success', 'Note updated successfully.');
    }

    public function togglePin(int $id): RedirectResponse
    {
        $note = Note::with('notable')->findOrFail($id);
        $this->authorizeNoteAccess($note, 'update');

        $this->noteService->togglePin($note);

        return redirect()->back()->with('success', $note->is_pinned ? 'Note pinned.' : 'Note unpinned.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->noteService->applyUserScope(Auth::user());
        $note = Note::with('notable')->findOrFail($id);
        $this->authorizeNoteAccess($note, 'delete');

        $note->delete();

        return redirect()->route('notes.index')->with('success', 'Note deleted successfully.');
    }

    public function restore($id)
    {
        $this->noteService->applyUserScope(Auth::user());
        $model = Note::withTrashed()->with('notable')->findOrFail($id);
        $this->authorizeNoteAccess($model, 'delete');

        $model->restore();

        return redirect()->route('notes.index')
            ->with('success', __('Note restored successfully.'));
    }

    public function forceDelete($id)
    {
        $this->noteService->applyUserScope(Auth::user());
        $model = Note::withTrashed()->with('notable')->findOrFail($id);
        $this->authorizeNoteAccess($model, 'delete');

        $model->forceDelete();

        return redirect()->route('notes.index')
            ->with('success', __('Note permanently deleted.'));
    }

    private function authorizeNoteAccess(Note $note, string $action): void
    {
        $user = Auth::user();
        if ($user->hasRole('super-admin')) { return; }
        if ($note->notable instanceof \App\Models\Module) {
            if ($action === 'delete') {
                abort(403, 'Forbidden');
            }
            abort_unless($note->notable && $user->canOnModule($note->notable, $action), 403);
        }
        abort_unless($note->user_id === $user->id, 403);
    }
}
