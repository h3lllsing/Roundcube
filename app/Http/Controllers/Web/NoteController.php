<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function index(Request $request): View
    {
        $query = Note::with('user');

        if ($request->filled('search')) {
            $query->where('content', 'like', '%' . $request->search . '%');
        }

        $notes = $query->latest()->paginate(20);

        return view('notes.index', compact('notes'));
    }

    public function create(): View
    {
        return view('notes.create');
    }

    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();
        $validated['notable_type'] = null;
        $validated['notable_id'] = null;

        Note::create($validated);

        return redirect()->route('notes.index')->with('success', 'Note created successfully.');
    }

    public function show(int $id): View
    {
        $note = Note::with('user')->findOrFail($id);

        return view('notes.show', compact('note'));
    }

    public function edit(int $id): View
    {
        $note = Note::findOrFail($id);

        return view('notes.edit', compact('note'));
    }

    public function update(UpdateNoteRequest $request, int $id): RedirectResponse
    {
        $note = Note::findOrFail($id);

        $note->update($request->validated());

        return redirect()->route('notes.index')->with('success', 'Note updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $note = Note::findOrFail($id);
        $note->delete();

        return redirect()->route('notes.index')->with('success', 'Note deleted successfully.');
    }
}
