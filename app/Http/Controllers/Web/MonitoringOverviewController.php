<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Voip;
use App\Models\Vps;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MonitoringOverviewController extends Controller
{
    private array $modelMap = [
        'domain' => ['class' => Domain::class, 'label' => 'Domain', 'nameCol' => 'name', 'route' => 'domains.show'],
        'hosting' => ['class' => Hosting::class, 'label' => 'Hosting', 'nameCol' => 'name', 'route' => 'hostings.show'],
        'vps' => ['class' => Vps::class, 'label' => 'VPS', 'nameCol' => 'name', 'route' => 'vps.show'],
        'voip' => ['class' => Voip::class, 'label' => 'VoIP', 'nameCol' => 'name', 'route' => 'voip.show'],
        'service_provider' => ['class' => ServiceProvider::class, 'label' => 'Service Provider', 'nameCol' => 'name', 'route' => 'service-providers.show'],
        'domain_email' => ['class' => DomainEmail::class, 'label' => 'Domain Email', 'nameCol' => 'email', 'route' => 'domain-emails.show'],
        'other_service' => ['class' => OtherService::class, 'label' => 'Other Service', 'nameCol' => 'name', 'route' => 'other-services.show'],
        'expiry_tracker' => ['class' => ExpiryTracker::class, 'label' => 'Renewal', 'nameCol' => 'name', 'route' => 'expiry-trackers.show'],
    ];

    public function index(Request $request): View
    {
        $user = Auth::user();
        $isSA = $user->hasRole('super-admin');

        $modelMap = $this->modelMap;
        if ($request->filled('type') && isset($modelMap[$request->type])) {
            $modelMap = [$request->type => $modelMap[$request->type]];
        }

        $items = collect();

        foreach ($modelMap as $typeKey => $cfg) {
            $nameCol = $cfg['nameCol'];
            $query = $cfg['class']::whereNotNull('monitoring_url')
                ->select('id', 'monitoring_url', 'last_ping_at', 'module_id', $nameCol);

            if (!$isSA) {
                $accessibleIds = $user->getAccessibleModuleIds('read');
                if ($accessibleIds !== null) {
                    $query->whereIn('module_id', $accessibleIds);
                }
            }

            $records = $query->get();
            foreach ($records as $record) {
                $lastPing = $record->last_ping_at;
                if ($lastPing === null) {
                    $status = 'unchecked';
                } elseif ($lastPing > now()->subHours(2)) {
                    $status = 'online';
                } else {
                    $status = 'offline';
                }

                $items->push((object) [
                    'type_key' => $typeKey,
                    'type_label' => $cfg['label'],
                    'id' => $record->id,
                    'name' => $record->getAttribute($nameCol),
                    'url' => $record->monitoring_url,
                    'last_ping_at' => $lastPing,
                    'status' => $status,
                    'route' => $cfg['route'],
                ]);
            }
        }

        if ($request->filled('status')) {
            $items = $items->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $items = $items->where('type_key', $request->type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $items = $items->filter(fn ($i) => str_contains(strtolower($i->name), strtolower($search))
                || str_contains(strtolower($i->url), strtolower($search)));
        }

        $allowedSortFields = ['type_key', 'type_label', 'id', 'name', 'url', 'last_ping_at', 'status', 'route'];
        $sortField = in_array($request->input('sort'), $allowedSortFields, true) ? $request->input('sort') : 'status';
        $sortDir = in_array($request->input('direction'), ['asc', 'desc'], true) ? $request->input('direction') : 'asc';
        $items = $sortDir === 'asc' ? $items->sortBy($sortField) : $items->sortByDesc($sortField);

        $perPage = config('app.pagination_per_page', 25);
        $page = $request->input('page', 1);

        $paginator = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $stats = $this->computeStats($items);

        return view('monitoring.index', [
            'items' => $paginator,
            'stats' => $stats,
            'sourceTypes' => collect($this->modelMap)->mapWithKeys(fn ($c, $k) => [$k => $c['label']]),
        ]);
    }

    private function computeStats($items): array
    {
        $total = $items->count();
        $online = $items->where('status', 'online')->count();
        $offline = $items->where('status', 'offline')->count();
        $unchecked = $items->where('status', 'unchecked')->count();

        return compact('total', 'online', 'offline', 'unchecked');
    }
}
