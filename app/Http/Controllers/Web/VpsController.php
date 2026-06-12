<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVpsRequest;
use App\Http\Requests\UpdateVpsRequest;
use App\Models\Module;
use App\Models\User;
use App\Models\Vps;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VpsController extends Controller
{
    public function index(Request $request): View
    {
        $query = Vps::with('module');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $vpsList = $query->latest()->paginate(20);

        return view('vps.index', compact('vpsList'));
    }

    public function create(): View
    {
        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');
        return view('vps.create', compact('modules', 'users'));
    }

    public function store(StoreVpsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $validated['user_id'] ?? Auth::id();

        Vps::create($validated);

        return redirect()->route('vps.index')->with('success', 'VPS created successfully.');
    }

    public function show(int $id): View
    {
        $vps = Vps::with('module')->findOrFail($id);
        return view('vps.show', compact('vps'));
    }

    public function edit(int $id): View
    {
        $vps = Vps::findOrFail($id);
        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');
        return view('vps.edit', compact('vps', 'modules', 'users'));
    }

    public function update(UpdateVpsRequest $request, int $id): RedirectResponse
    {
        $vps = Vps::findOrFail($id);

        $vps->update($request->validated());

        return redirect()->route('vps.index')->with('success', 'VPS updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $vps = Vps::findOrFail($id);
        $vps->delete();

        return redirect()->route('vps.index')->with('success', 'VPS deleted successfully.');
    }
}
