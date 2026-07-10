<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\View\Component;
use Spatie\Activitylog\Models\Activity;

class ActivityTimeline extends Component
{
    public function __construct(
        public string $subjectType,
        public int $subjectId,
    ) {}

    public function render(): View|Closure|string
    {
        $map = Relation::$morphMap;
        $subjectAlias = is_string($map) ? $map : (array_search($this->subjectType, $map, true) ?: $this->subjectType);
        $activities = Activity::where('subject_type', $subjectAlias)
            ->where('subject_id', $this->subjectId)
            ->with('causer')
            ->latest()
            ->get();

        return view('components.activity-timeline', ['activities' => $activities]);
    }
}
