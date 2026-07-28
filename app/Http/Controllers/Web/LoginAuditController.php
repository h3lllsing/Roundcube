<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LoginAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginAuditController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', LoginAudit::class);

        $query = LoginAudit::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', '%' . $search . '%')
                    ->orWhere('ip_address', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $audits = $query->with('user')->latest()->paginate(50);

        return view('login-audits.index', compact('audits'));
    }

    public function show(int $id): View
    {
        $this->authorize('view', LoginAudit::class);

        $audit = LoginAudit::with('user')->findOrFail($id);
        return view('login-audits.show', compact('audit'));
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', LoginAudit::class);

        LoginAudit::findOrFail($id)->delete();
        return redirect()->route('login-audits.index')->with('success', 'Login audit deleted.');
    }
}
