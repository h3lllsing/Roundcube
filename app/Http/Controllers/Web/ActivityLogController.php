<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {}

    public function index(Request $request): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $filters = $request->only(['event', 'search', 'causer_id', 'date_from', 'date_to']);
        $activities = $this->activityLogService->paginate($filters);
        $users = $this->activityLogService->getUsers();

        return view('activity-logs.index', compact('activities', 'users'));
    }

    public function show(int $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $activity = $this->activityLogService->find($id);

        return view('activity-logs.show', compact('activity'));
    }
}
