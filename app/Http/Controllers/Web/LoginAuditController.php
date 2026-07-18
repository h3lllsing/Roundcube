<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LoginAudit;
use Illuminate\View\View;

class LoginAuditController extends Controller
{
    public function index(): View
    {
        $audits = LoginAudit::latest()->paginate(50);
        return view('login-audits.index', compact('audits'));
    }

    public function show(int $id): View
    {
        $audit = LoginAudit::findOrFail($id);
        return view('login-audits.show', compact('audit'));
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        LoginAudit::findOrFail($id)->delete();
        return redirect()->route('login-audits.index')->with('success', 'Login audit deleted.');
    }
}
