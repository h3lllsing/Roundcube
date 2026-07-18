<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        $activities = Activity::with('causer')->latest()->paginate(50);
        $users = User::orderBy('name')->pluck('name', 'id');
        return view('activity-logs.index', compact('activities', 'users'));
    }

    public function show(int $id): View
    {
        $activity = Activity::with('causer')->findOrFail($id);
        return view('activity-logs.show', compact('activity'));
    }
}
