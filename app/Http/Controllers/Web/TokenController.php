<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TokenController extends Controller
{
    public function index(Request $request): View
    {
        $query = Auth::user()->tokens()->orderByDesc('id');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $tokens = $query->select(['id', 'name', 'created_at', 'last_used_at'])->paginate(15);

        return view('tokens.index', compact('tokens'));
    }

    public function create(): View
    {
        return view('tokens.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $token = Auth::user()->createToken($validated['name']);

        activity()->event('created')
            ->causedBy(Auth::user())
            ->withProperties([
                'token_name' => $validated['name'],
                'token_id' => $token->accessToken->id,
            ])
            ->log('API token created: '.$validated['name']);

        return redirect()->route('tokens.index')
            ->with('success', 'Token created successfully.')
            ->with('plain_text', $token->plainTextToken);
    }

    public function destroy(int $id): RedirectResponse
    {
        $token = Auth::user()->tokens()->where('id', $id)->first();

        if (! $token) {
            return redirect()->back()->with('error', 'Token not found.');
        }

        $tokenName = $token->name;
        $token->delete();

        activity()->event('deleted')
            ->causedBy(Auth::user())
            ->withProperties([
                'token_name' => $tokenName,
                'token_id' => $id,
            ])
            ->log('API token revoked: '.$tokenName);

        return redirect()->route('tokens.index')->with('success', 'Token revoked successfully.');
    }
}
