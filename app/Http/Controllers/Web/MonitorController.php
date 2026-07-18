<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\MonitorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MonitorController extends Controller
{
    public function check(Request $request, string $type, int $id): RedirectResponse
    {
        return redirect()->back()->with('error', 'Monitoring is not available for this resource type.');
    }
}
