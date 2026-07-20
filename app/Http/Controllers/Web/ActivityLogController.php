<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Activity::class);

        $query = Activity::with('causer');

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->latest()->paginate(50);
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('activity-logs.index', compact('activities', 'users'));
    }

    public function show(int $id): View
    {
        $this->authorize('view', Activity::class);

        $activity = Activity::with('causer')->findOrFail($id);
        return view('activity-logs.show', compact('activity'));
    }
}
