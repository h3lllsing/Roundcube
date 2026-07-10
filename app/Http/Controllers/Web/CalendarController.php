<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __construct(
        private readonly CalendarService $calendarService
    ) {}

    public function index(Request $request): View
    {
        $month = max(1, min(12, (int) $request->get('month', now()->month)));
        $year = max(2000, min(2099, (int) $request->get('year', now()->year)));
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $events = $this->calendarService->getEvents(Auth::user(), $month, $year);

        $prevMonth = $month === 1 ? 12 : $month - 1;
        $prevYear = $month === 1 ? $year - 1 : $year;
        $nextMonth = $month === 12 ? 1 : $month + 1;
        $nextYear = $month === 12 ? $year + 1 : $year;

        $calendar = [];
        $current = clone $start;
        $lastDay = (clone $end)->day;
        $startDayOfWeek = (int) $current->dayOfWeek;

        $day = 1;
        $week = array_fill(0, $startDayOfWeek, null);
        while ($day <= $lastDay) {
            $week[] = $day;
            if (count($week) === 7) {
                $calendar[] = $week;
                $week = [];
            }
            $day++;
        }
        if (count($week) > 0) {
            $week = array_pad($week, 7, null);
            $calendar[] = $week;
        }

        $eventsByDate = [];
        foreach ($events as $event) {
            $eventsByDate[$event['date']][] = $event;
        }

        return view('calendar.index', compact(
            'month', 'year', 'start', 'end', 'events',
            'prevMonth', 'prevYear', 'nextMonth', 'nextYear',
            'calendar', 'eventsByDate',
        ));
    }
}
