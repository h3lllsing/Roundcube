<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHostingRequest;
use App\Http\Requests\UpdateHostingRequest;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HostingController extends Controller
{
    public function index(Request $request): View
    {
        $query = Hosting::with('module');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $hostings = $query->latest()->paginate(20);

        return view('hostings.index', compact('hostings'));
    }

    public function create(): View
    {
        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');
        return view('hostings.create', compact('modules', 'users'));
    }

    public function store(StoreHostingRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $validated['user_id'] ?? Auth::id();

        Hosting::create($validated);

        return redirect()->route('hostings.index')->with('success', 'Hosting created successfully.');
    }

    public function show(int $id): View
    {
        $hosting = Hosting::with('module')->findOrFail($id);
        return view('hostings.show', compact('hosting'));
    }

    public function edit(int $id): View
    {
        $hosting = Hosting::findOrFail($id);
        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');
        return view('hostings.edit', compact('hosting', 'modules', 'users'));
    }

    public function update(UpdateHostingRequest $request, int $id): RedirectResponse
    {
        $hosting = Hosting::findOrFail($id);

        $hosting->update($request->validated());

        return redirect()->route('hostings.index')->with('success', 'Hosting updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $hosting = Hosting::findOrFail($id);
        $hosting->delete();

        return redirect()->route('hostings.index')->with('success', 'Hosting deleted successfully.');
    }
}
