<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Feature;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\Note;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Task;
use App\Models\User;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class SearchController extends Controller
{
    #[OA\Get(
        path: '/search',
        summary: 'Global search across all modules',
        security: [['sanctum' => []]],
        tags: ['Search'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 5)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Search results grouped by type'),
        ]
    )]
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = $request->get('q');
        $limit = min((int) $request->get('limit', 5), 20);

        if (!$q || strlen(trim($q)) < 2) {
            return $this->success([]);
        }

        $cacheKey = 'search:' . $request->user()->id . ':' . md5(strtolower(trim($q)) . '|' . $limit);

        $results = Cache::remember($cacheKey, 60, function () use ($q, $limit, $request) {
            $user = $request->user();
            $isSuperAdmin = $user->hasRole('super-admin');
            $term = '%' . trim($q) . '%';

            $results = [];

            $accessibleModuleIds = $isSuperAdmin ? null : Module::whereHas('rolePermissions', function ($q) use ($user) {
                $q->whereIn('role_id', $user->roles()->pluck('roles.id'))
                  ->where('can_read', true);
            })->pluck('id');

            $searches = [
                'features' => ['model' => Feature::class, 'label' => 'Features', 'column' => 'name', 'route' => '/features'],
                'modules' => ['model' => Module::class, 'label' => 'Modules', 'column' => 'name', 'route' => '/modules'],
                'tasks' => ['model' => Task::class, 'label' => 'Tasks', 'column' => 'title', 'route' => '/tasks'],
                'vault' => ['model' => VaultEntry::class, 'label' => 'Vault', 'column' => 'service_name', 'route' => '/vault'],
                'users' => ['model' => User::class, 'label' => 'Users', 'column' => 'name', 'route' => '/users'],
                'notes' => ['model' => Note::class, 'label' => 'Notes', 'column' => 'content', 'route' => '/notes'],
                'domains' => ['model' => Domain::class, 'label' => 'Domains', 'column' => 'name', 'route' => '/domains'],
                'hostings' => ['model' => Hosting::class, 'label' => 'Hostings', 'column' => 'name', 'route' => '/hostings'],
                'vps' => ['model' => Vps::class, 'label' => 'VPS', 'column' => 'name', 'route' => '/vps'],
                'voip' => ['model' => Voip::class, 'label' => 'VoIP', 'column' => 'name', 'route' => '/voip'],
                'service_providers' => ['model' => ServiceProvider::class, 'label' => 'Service Providers', 'column' => 'name', 'route' => '/service-providers'],
                'other_services' => ['model' => OtherService::class, 'label' => 'Other Services', 'column' => 'name', 'route' => '/other-services'],
                'expiry_trackers' => ['model' => ExpiryTracker::class, 'label' => 'Expiry Trackers', 'column' => 'name', 'route' => '/expiry-trackers'],
                'domain_emails' => ['model' => DomainEmail::class, 'label' => 'Domain Emails', 'column' => 'email', 'route' => '/domain-emails'],
            ];

            $userOwnedTypes = ['domains', 'hostings', 'vps', 'voip', 'service_providers', 'domain_emails', 'other_services', 'expiry_trackers', 'vault', 'notes'];

            $moduleScopedTypes = ['tasks'];

            foreach ($searches as $key => $cfg) {
                $column = $cfg['column'];
                $query = $cfg['model']::where($column, 'like', $term);

                if (!$isSuperAdmin) {
                    if (in_array($key, $userOwnedTypes)) {
                        $query->where('user_id', $user->id);
                    } elseif (in_array($key, $moduleScopedTypes) && $accessibleModuleIds !== null && $accessibleModuleIds->isNotEmpty()) {
                        $query->whereIn('module_id', $accessibleModuleIds);
                    }
                }

                $items = $query->limit($limit)
                    ->get(['id', $column, 'status'])
                    ->map(function ($item) use ($column, $cfg, $key) {
                        return [
                            'id' => $item->id,
                            'title' => $item->getAttribute($column),
                            'url' => $cfg['route'] . '/' . $item->id,
                            'type' => $key,
                            'type_label' => $cfg['label'],
                            'status' => $item->getAttribute('status'),
                        ];
                    });

                if ($items->isNotEmpty()) {
                    $results[$key] = [
                        'label' => $cfg['label'],
                        'items' => $items,
                        'url' => $cfg['route'],
                    ];
                }
            }

            return $results;
        });

        return $this->success($results);
    }
}
