<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $user->loadMissing('roles');
        $data = app(DashboardService::class)->compute($user);

        return view('dashboard.index', $data);
    }
}
