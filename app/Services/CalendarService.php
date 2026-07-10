<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Task;
use App\Models\User;
use App\Models\Voip;
use App\Models\Vps;
use Carbon\Carbon;

class CalendarService
{
    private array $serviceModels = [
        'domains' => ['model' => Domain::class, 'label' => 'Domain'],
        'hostings' => ['model' => Hosting::class, 'label' => 'Hosting'],
        'vps' => ['model' => Vps::class, 'label' => 'VPS'],
        'voip' => ['model' => Voip::class, 'label' => 'VoIP'],
        'service-providers' => ['model' => ServiceProvider::class, 'label' => 'Service Provider'],
        'domain-emails' => ['model' => DomainEmail::class, 'label' => 'Domain Email'],
        'other-services' => ['model' => OtherService::class, 'label' => 'Other Service'],
        'expiry-trackers' => ['model' => ExpiryTracker::class, 'label' => 'Renewal'],
    ];

    public function getEvents(User $user, int $month, int $year): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $accessibleIds = [];
        if (! $user->hasRole('super-admin')) {
            $accessibleIds = $user->getAccessibleModuleIds('read');
        }

        $events = [];
        foreach ($this->serviceModels as $key => $cfg) {
            $query = $cfg['model']::whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', $start)
                ->whereDate('expiry_date', '<=', $end);
            if (! $user->hasRole('super-admin')) {
                if (! empty($accessibleIds)) {
                    $query->whereIn('module_id', $accessibleIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
            $items = $query->orderBy('expiry_date')->take(500)->get();

            foreach ($items as $item) {
                $events[] = [
                    'date' => Carbon::parse($item->expiry_date)->toDateString(),
                    'id' => $item->id,
                    'name' => $item->name ?? $item->email ?? 'Unnamed',
                    'type' => $key,
                    'type_label' => $cfg['label'],
                    'status' => $item->status,
                ];
            }
        }

        $taskQuery = Task::whereNotNull('due_date')
            ->whereDate('due_date', '>=', $start)
            ->whereDate('due_date', '<=', $end);
        if (! $user->hasRole('super-admin')) {
            $taskQuery->whereHas('assignees', fn ($q) => $q->where('user_id', $user->id));
        }
        foreach ($taskQuery->orderBy('due_date')->take(500)->get() as $task) {
            $events[] = [
                'date' => Carbon::parse($task->due_date)->toDateString(),
                'id' => $task->id,
                'name' => $task->title,
                'type' => 'tasks',
                'type_label' => 'Task',
                'status' => $task->status,
            ];
        }

        usort($events, fn ($a, $b) => $a['date'] <=> $b['date']);

        return $events;
    }

    public function getServiceModels(): array
    {
        return $this->serviceModels;
    }
}
