<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoipRequest;
use App\Http\Requests\UpdateVoipRequest;
use App\Models\Module;
use App\Models\User;
use App\Models\Voip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VoipController extends Controller
{
    public function index(Request $request): View
    {
        $query = Voip::with('module');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $voipList = $query->latest()->paginate(20);

        return view('voip.index', compact('voipList'));
    }

    public function create(): View
    {
        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');
        return view('voip.create', compact('modules', 'users'));
    }

    public function store(StoreVoipRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $validated['user_id'] ?? Auth::id();

        Voip::create($validated);

        return redirect()->route('voip.index')->with('success', 'VoIP created successfully.');
    }

    public function show(int $id): View
    {
        $voip = Voip::with('module')->findOrFail($id);
        return view('voip.show', compact('voip'));
    }

    public function edit(int $id): View
    {
        $voip = Voip::findOrFail($id);
        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');
        return view('voip.edit', compact('voip', 'modules', 'users'));
    }

    public function update(UpdateVoipRequest $request, int $id): RedirectResponse
    {
        $voip = Voip::findOrFail($id);

        $voip->update($request->validated());

        return redirect()->route('voip.index')->with('success', 'VoIP updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $voip = Voip::findOrFail($id);
        $voip->delete();

        return redirect()->route('voip.index')->with('success', 'VoIP deleted successfully.');
    }
}
