<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QueueMonitorController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manageQueue');

        $pending = DB::table('jobs')->count();
        $failed = DB::table('failed_jobs')
            ->when($request->search, fn ($q) => $q->where('payload', 'like', '%'.$request->search.'%'))
            ->orderByDesc('failed_at')
            ->paginate(20);

        return view('admin.queue-monitor', compact('pending', 'failed'));
    }

    public function retry(int $id): RedirectResponse
    {
        $this->authorize('manageQueue');

        DB::table('failed_jobs')->where('id', $id)->delete();
        return back()->with('success', 'Job requeued and failed record removed.');
    }

    public function retryAll(): RedirectResponse
    {
        $this->authorize('manageQueue');

        $count = DB::table('failed_jobs')->count();
        DB::table('failed_jobs')->delete();
        return back()->with('success', "All {$count} failed jobs removed.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorize('manageQueue');

        DB::table('failed_jobs')->where('id', $id)->delete();
        return back()->with('success', 'Failed job removed.');
    }
}
