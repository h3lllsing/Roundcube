<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CalendarService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct(
        private readonly CalendarService $calendarService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $month = max(1, min(12, (int) $request->get('month', now()->month)));
        $year = max(2000, min(2099, (int) $request->get('year', now()->year)));
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $events = $this->calendarService->getEvents($request->user(), $month, $year);

        return $this->success([
            'month' => $month,
            'year' => $year,
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'events' => $events,
        ]);
    }
}
