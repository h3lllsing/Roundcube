<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Voip;
use App\Models\Vps;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $isSuperAdmin = $user->hasRole('super-admin');
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $serviceModels = [
            'domains' => ['model' => Domain::class, 'label' => 'Domain'],
            'hostings' => ['model' => Hosting::class, 'label' => 'Hosting'],
            'vps' => ['model' => Vps::class, 'label' => 'VPS'],
            'voip' => ['model' => Voip::class, 'label' => 'VoIP'],
            'service-providers' => ['model' => ServiceProvider::class, 'label' => 'Service Provider'],
            'domain-emails' => ['model' => DomainEmail::class, 'label' => 'Domain Email'],
            'other-services' => ['model' => OtherService::class, 'label' => 'Other Service'],
            'expiry-trackers' => ['model' => ExpiryTracker::class, 'label' => 'Expiry Tracker'],
        ];

        $events = [];
        foreach ($serviceModels as $key => $cfg) {
            $query = $cfg['model']::whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', $start)
                ->whereDate('expiry_date', '<=', $end);
            if (!$isSuperAdmin) {
                $query->where('user_id', $user->id);
            }
            $items = $query->orderBy('expiry_date')->get();

            foreach ($items as $item) {
                $date = Carbon::parse($item->expiry_date)->toDateString();
                $events[] = [
                    'date' => $date,
                    'id' => $item->id,
                    'name' => $item->name ?? $item->email ?? 'Unnamed',
                    'type' => $key,
                    'type_label' => $cfg['label'],
                    'status' => $item->status,
                ];
            }
        }

        usort($events, fn($a, $b) => $a['date'] <=> $b['date']);

        return $this->success([
            'month' => $month,
            'year' => $year,
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'events' => $events,
        ]);
    }
}
