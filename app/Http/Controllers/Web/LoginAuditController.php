<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\LoginAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginAuditController extends Controller
{
    public function __construct(
        private readonly LoginAuditService $loginAuditService
    ) {}

    public function index(Request $request): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $filters = $request->only(['search', 'event', 'date_from', 'date_to']);
        $audits = $this->loginAuditService->paginate($filters);

        return view('login-audits.index', compact('audits'));
    }

    public function show(int $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $audit = $this->loginAuditService->find($id);

        return view('login-audits.show', compact('audit'));
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $this->loginAuditService->delete($id, Auth::user());

        return redirect()->route('login-audits.index')->with('success', 'Login audit record deleted.');
    }
}
