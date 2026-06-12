<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVaultRequest;
use App\Http\Requests\UpdateVaultRequest;
use App\Models\Module;
use App\Models\User;
use App\Models\VaultEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VaultController extends Controller
{
    public function index(Request $request): View
    {
        $query = VaultEntry::with('module');

        if ($request->filled('search')) {
            $query->where('service_name', 'like', '%' . $request->search . '%');
        }

        $entries = $query->latest()->paginate(20);

        return view('vault.index', compact('entries'));
    }

    public function create(): View
    {
        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');
        return view('vault.create', compact('modules', 'users'));
    }

    public function store(StoreVaultRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $validated['user_id'] ?? Auth::id();

        $entry = VaultEntry::create($validated);

        if ($request->filled('encrypted_password') || $request->filled('password')) {
            $entry->encryptPassword($request->filled('encrypted_password') ? $request->encrypted_password : $request->password);
            $entry->save();
        }

        return redirect()->route('vault.index')->with('success', 'Vault entry created successfully.');
    }

    public function show(int $id): View
    {
        $entry = VaultEntry::with('module')->findOrFail($id);
        return view('vault.show', compact('entry'));
    }

    public function edit(int $id): View
    {
        $entry = VaultEntry::findOrFail($id);
        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');
        return view('vault.edit', compact('entry', 'modules', 'users'));
    }

    public function update(UpdateVaultRequest $request, int $id): RedirectResponse
    {
        $entry = VaultEntry::findOrFail($id);

        $entry->update($request->validated());

        if ($request->filled('encrypted_password') || $request->filled('password')) {
            $entry->encryptPassword($request->filled('encrypted_password') ? $request->encrypted_password : $request->password);
            $entry->save();
        }

        return redirect()->route('vault.index')->with('success', 'Vault entry updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $entry = VaultEntry::findOrFail($id);
        $entry->delete();

        return redirect()->route('vault.index')->with('success', 'Vault entry deleted successfully.');
    }
}
