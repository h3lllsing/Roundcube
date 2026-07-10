<?php

namespace App\Services;

use App\Helpers\SearchHelper;
use App\Models\Asset;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Feature;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\Note;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\SmtpProfile;
use App\Models\Task;
use App\Models\User;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use App\Services\ReportService;
use Illuminate\Support\Collection;

class GlobalSearchService
{
    private const FILTER_ALL = 'all';
    private const FILTER_SERVICES = 'services';
    private const FILTER_ASSETS = 'assets';
    private const FILTER_TASKS = 'tasks';
    private const FILTER_VAULT = 'vault';
    private const FILTER_USERS = 'users';

    private const MAX_PER_MODULE = 5;
    private const MAX_TOTAL = 50;

    private array $modules;

    public function __construct()
    {
        $this->modules = [
            'domains' => [
                'model' => Domain::class,
                'label' => 'Domains',
                'columns' => ['name', 'description'],
                'title_col' => 'name',
                'subtitle_cols' => [],
                'badge_col' => 'status',
                'route' => 'domains.show',
                'index_route' => 'domains.index',
                'filter' => self::FILTER_SERVICES,
                'ownership' => 'user_or_module',
                'slug' => 'domains',
            ],
            'hostings' => [
                'model' => Hosting::class,
                'label' => 'Hosting',
                'columns' => ['name', 'username', 'domain', 'cpanel_ip', 'domain_ip'],
                'title_col' => 'name',
                'subtitle_cols' => ['domain', 'username'],
                'badge_col' => 'status',
                'route' => 'hostings.show',
                'index_route' => 'hostings.index',
                'filter' => self::FILTER_SERVICES,
                'ownership' => 'user_or_module',
                'slug' => 'hostings',
            ],
            'vps' => [
                'model' => Vps::class,
                'label' => 'VPS',
                'columns' => ['name', 'ip_address', 'os', 'department', 'location'],
                'title_col' => 'name',
                'subtitle_cols' => ['ip_address', 'os'],
                'badge_col' => 'status',
                'route' => 'vps.show',
                'index_route' => 'vps.index',
                'filter' => self::FILTER_SERVICES,
                'ownership' => 'user_or_module',
                'slug' => 'vps',
            ],
            'voip' => [
                'model' => Voip::class,
                'label' => 'VoIP',
                'columns' => ['name', 'phone_number', 'username', 'server_ip'],
                'title_col' => 'name',
                'subtitle_cols' => ['phone_number', 'server_ip'],
                'badge_col' => 'status',
                'route' => 'voip.show',
                'index_route' => 'voip.index',
                'filter' => self::FILTER_SERVICES,
                'ownership' => 'user_or_module',
                'slug' => 'voip',
            ],
            'domain_emails' => [
                'model' => DomainEmail::class,
                'label' => 'Domain Emails',
                'columns' => ['email'],
                'title_col' => 'email',
                'subtitle_cols' => [],
                'badge_col' => 'status',
                'route' => 'domain-emails.show',
                'index_route' => 'domain-emails.index',
                'filter' => self::FILTER_SERVICES,
                'ownership' => 'user_or_module',
                'slug' => 'domain-emails',
            ],
            'other_services' => [
                'model' => OtherService::class,
                'label' => 'Other Services',
                'columns' => ['name', 'service_type', 'username'],
                'title_col' => 'name',
                'subtitle_cols' => ['service_type', 'username'],
                'badge_col' => 'status',
                'route' => 'other-services.show',
                'index_route' => 'other-services.index',
                'filter' => self::FILTER_SERVICES,
                'ownership' => 'user_or_module',
                'slug' => 'other-services',
            ],
            'service_providers' => [
                'model' => ServiceProvider::class,
                'label' => 'Service Providers',
                'columns' => ['name'],
                'title_col' => 'name',
                'subtitle_cols' => [],
                'badge_col' => 'status',
                'route' => 'service-providers.show',
                'index_route' => 'service-providers.index',
                'filter' => self::FILTER_SERVICES,
                'ownership' => 'user_or_module',
                'slug' => 'service-providers',
            ],
            'expiry_trackers' => [
                'model' => ExpiryTracker::class,
                'label' => 'Renewals',
                'columns' => ['name', 'username', 'login_url'],
                'title_col' => 'name',
                'subtitle_cols' => ['expiry_date'],
                'badge_col' => 'status',
                'route' => 'expiry-trackers.show',
                'index_route' => 'expiry-trackers.index',
                'filter' => self::FILTER_SERVICES,
                'ownership' => 'user_or_module',
                'slug' => 'expiry-trackers',
            ],
            'assets' => [
                'model' => Asset::class,
                'label' => 'Assets',
                'columns' => ['asset_tag', 'serial_number', 'department', 'description'],
                'title_col' => 'asset_tag',
                'subtitle_cols' => ['serial_number'],
                'badge_col' => 'status',
                'route' => 'assets.show',
                'index_route' => 'assets.index',
                'filter' => self::FILTER_ASSETS,
                'ownership' => 'user_or_module',
                'slug' => 'assets',
            ],
            'tasks' => [
                'model' => Task::class,
                'label' => 'Tasks',
                'columns' => ['title', 'description'],
                'title_col' => 'title',
                'subtitle_cols' => [],
                'badge_col' => 'status',
                'route' => 'tasks.show',
                'index_route' => 'tasks.index',
                'filter' => self::FILTER_TASKS,
                'ownership' => 'task',
                'slug' => null,
            ],
            'vault' => [
                'model' => VaultEntry::class,
                'label' => 'Vault',
                'columns' => ['service_name', 'service_url', 'username', 'description'],
                'title_col' => 'service_name',
                'subtitle_cols' => ['username', 'service_url'],
                'badge_col' => null,
                'route' => 'vault.show',
                'index_route' => 'vault.index',
                'filter' => self::FILTER_VAULT,
                'ownership' => 'user_or_module',
                'slug' => 'vault',
            ],
            'notes' => [
                'model' => Note::class,
                'label' => 'Notes',
                'columns' => ['content'],
                'title_col' => 'content',
                'subtitle_cols' => [],
                'badge_col' => null,
                'route' => 'notes.show',
                'index_route' => 'notes.index',
                'filter' => self::FILTER_ALL,
                'ownership' => 'user',
                'slug' => null,
            ],
            'features' => [
                'model' => Feature::class,
                'label' => 'Features',
                'columns' => ['name'],
                'title_col' => 'name',
                'subtitle_cols' => [],
                'badge_col' => null,
                'route' => 'features.show',
                'index_route' => 'features.index',
                'filter' => self::FILTER_ALL,
                'ownership' => 'sa_only',
                'slug' => null,
            ],
            'modules' => [
                'model' => Module::class,
                'label' => 'Modules',
                'columns' => ['name'],
                'title_col' => 'name',
                'subtitle_cols' => [],
                'badge_col' => null,
                'route' => 'modules.show',
                'index_route' => 'modules.index',
                'filter' => self::FILTER_ALL,
                'ownership' => 'sa_only',
                'slug' => null,
            ],
            'users' => [
                'model' => User::class,
                'label' => 'Users',
                'columns' => ['name', 'email'],
                'title_col' => 'name',
                'subtitle_cols' => ['email'],
                'badge_col' => null,
                'route' => 'users.show',
                'index_route' => 'users.index',
                'filter' => self::FILTER_USERS,
                'ownership' => 'sa_only',
                'slug' => null,
            ],
            'smtp_profiles' => [
                'model' => SmtpProfile::class,
                'label' => 'SMTP Profiles',
                'columns' => ['name', 'sender_name', 'sender_email', 'smtp_host', 'smtp_username'],
                'title_col' => 'name',
                'subtitle_cols' => ['sender_email', 'smtp_host'],
                'badge_col' => 'is_active',
                'route' => 'smtp-profiles.show',
                'index_route' => 'smtp-profiles.index',
                'filter' => self::FILTER_ALL,
                'ownership' => 'sa_only',
                'slug' => null,
            ],
            'reports' => [
                'model' => null,
                'label' => 'Reports',
                'columns' => [],
                'title_col' => 'label',
                'subtitle_cols' => ['description'],
                'badge_col' => null,
                'route' => null,
                'index_route' => 'reports.index',
                'filter' => self::FILTER_ALL,
                'ownership' => 'sa_only',
                'slug' => null,
            ],
        ];
    }

    public function search(string $query, User $user, string $filter = self::FILTER_ALL, int $limit = self::MAX_PER_MODULE): array
    {
        $query = trim($query);
        if (strlen($query) < 2) {
            return [];
        }

        $isSA = $user->hasRole('super-admin');
        $accessibleIds = $isSA ? null : $user->getAccessibleModuleIds('read');
        $term = '%' . $query . '%';
        $results = [];
        $totalCount = 0;

        foreach ($this->modules as $key => $cfg) {
            if ($filter !== self::FILTER_ALL && $cfg['filter'] !== $filter) {
                continue;
            }

            $moduleResults = $this->searchModule($key, $cfg, $query, $term, $user, $isSA, $accessibleIds, $limit);
            if (!empty($moduleResults)) {
                $results[$key] = $moduleResults;
                $totalCount += count($moduleResults['items']);
                if ($totalCount >= self::MAX_TOTAL) {
                    break;
                }
            }
        }

        return $results;
    }

    public function searchForApi(string $query, User $user, string $filter = self::FILTER_ALL, int $limit = self::MAX_PER_MODULE): array
    {
        $results = $this->search($query, $user, $filter, $limit);
        $groups = [];

        foreach ($results as $key => $group) {
            $groups[] = [
                'key' => $key,
                'label' => $group['label'],
                'url' => route($group['index_route']),
                'items' => $group['items'],
            ];
        }

        return $groups;
    }

    private function searchModule(string $key, array $cfg, string $rawQuery, string $term, User $user, bool $isSA, $accessibleIds, int $limit = self::MAX_PER_MODULE): ?array
    {
        if ($cfg['model'] === null) {
            return $this->searchNullModel($key, $cfg, $rawQuery, $user, $limit);
        }

        $query = $cfg['model']::where(function ($q) use ($cfg, $term) {
            foreach ($cfg['columns'] as $col) {
                $q->orWhere($col, 'like', $term);
            }
        });

        $this->applyOwnership($query, $cfg, $user, $isSA, $accessibleIds);

        if (!empty($cfg['badge_col'])) {
            $selectCols = ['id', $cfg['title_col'], 'subtitle' => $cfg['subtitle_cols'] ? null : null];
            $cols = array_merge(['id', $cfg['title_col']], $cfg['subtitle_cols'], [$cfg['badge_col']]);
        } else {
            $cols = array_merge(['id', $cfg['title_col']], $cfg['subtitle_cols']);
        }

        $cols = array_unique($cols);

        $dbResults = $query->limit($limit)->get($cols);

        if ($dbResults->isEmpty()) {
            return null;
        }

        $items = $this->buildItems($dbResults, $cfg, $rawQuery);

        if (empty($items)) {
            return null;
        }

        return [
            'label' => $cfg['label'],
            'items' => $items,
            'index_route' => $cfg['index_route'],
        ];
    }

    private function searchNullModel(string $key, array $cfg, string $rawQuery, User $user, int $limit): ?array
    {
        if (!$user->hasRole('super-admin')) {
            return null;
        }

        $lower = strtolower($rawQuery);
        $items = [];
        $reportService = app(ReportService::class);
        $categories = $reportService->allCategories();

        foreach ($categories as $slug => $cat) {
            if (stripos($cat['label'], $rawQuery) !== false || stripos($cat['description'], $rawQuery) !== false) {
                $items[] = [
                    'id' => $slug,
                    'title' => $cat['label'],
                    'title_highlighted' => SearchHelper::highlight($cat['label'], $rawQuery),
                    'subtitle' => $cat['description'],
                    'subtitle_highlighted' => SearchHelper::highlight($cat['description'], $rawQuery),
                    'url' => route('reports.category', $slug),
                    'badge' => null,
                ];
                if (count($items) >= $limit) {
                    break;
                }
            }

            $data = $reportService->categoryReports($slug);
            if (!$data) {
                continue;
            }
            foreach ($data['reports'] as $reportSlug => $report) {
                if (count($items) >= $limit) {
                    break 2;
                }
                if (stripos($report['label'], $rawQuery) !== false || stripos($report['description'], $rawQuery) !== false) {
                    $items[] = [
                        'id' => "{$slug}.{$reportSlug}",
                        'title' => $report['label'],
                        'title_highlighted' => SearchHelper::highlight($report['label'], $rawQuery),
                        'subtitle' => $cat['label'] . ' &middot; ' . $report['description'],
                        'subtitle_highlighted' => SearchHelper::highlight($report['description'], $rawQuery),
                        'url' => route('reports.show', [$slug, $reportSlug]),
                        'badge' => null,
                    ];
                }
            }
        }

        if (empty($items)) {
            return null;
        }

        return [
            'label' => $cfg['label'],
            'items' => $items,
            'index_route' => $cfg['index_route'],
        ];
    }

    private function buildItems(Collection $dbResults, array $cfg, string $rawQuery): array
    {
        $items = [];

        foreach ($dbResults as $model) {
            $title = (string) $model->getAttribute($cfg['title_col']);

            $subtitle = '';
            if (!empty($cfg['subtitle_cols'])) {
                $parts = [];
                foreach ($cfg['subtitle_cols'] as $col) {
                    $val = $model->getAttribute($col);
                    if ($col === 'expiry_date' && $val) {
                        $val = 'Expires ' . $val->format('M d, Y');
                    }
                    if (!empty($val)) {
                        $parts[] = (string) $val;
                    }
                }
                $subtitle = implode(' · ', $parts);
            }

            $badge = $cfg['badge_col'] ? $model->getAttribute($cfg['badge_col']) : null;
            if ($badge === true) {
                $badge = 'active';
            } elseif ($badge === false) {
                $badge = 'inactive';
            }
            if ($badge !== null) {
                $badge = (string) $badge;
            }

            $routeParams = [];

            if (isset($cfg['route'])) {
                $routeName = $cfg['route'];
            } else {
                continue;
            }

            $items[] = [
                'id' => $model->id,
                'title' => $title,
                'title_highlighted' => SearchHelper::highlight($title, $rawQuery),
                'subtitle' => $subtitle,
                'subtitle_highlighted' => SearchHelper::highlight($subtitle, $rawQuery),
                'url' => route($routeName, [$model->id]),
                'badge' => $badge,
            ];
        }

        $lowerQuery = strtolower($rawQuery);
        usort($items, function ($a, $b) use ($lowerQuery) {
            return $this->relevanceScore($b['title'], $lowerQuery) - $this->relevanceScore($a['title'], $lowerQuery);
        });

        return $items;
    }

    private function relevanceScore(string $title, string $query): int
    {
        $lower = strtolower($title);
        if ($lower === $query) {
            return 3;
        }
        if (str_starts_with($lower, $query)) {
            return 2;
        }
        if (str_contains($lower, $query)) {
            return 1;
        }
        return 0;
    }

    private function applyOwnership($query, array $cfg, User $user, bool $isSA, $accessibleIds): void
    {
        if ($isSA) {
            return;
        }

        $ownership = $cfg['ownership'] ?? 'user';

        if ($ownership === 'sa_only') {
            $query->whereRaw('1 = 0');
            return;
        }

        if ($ownership === 'user') {
            $query->where('user_id', $user->id);
            return;
        }

        if ($ownership === 'task') {
            $query->where(function ($q) use ($accessibleIds, $user) {
                if ($accessibleIds !== null && !empty($accessibleIds)) {
                    $q->whereIn('module_id', $accessibleIds);
                }
                $q->orWhereHas('assignees', fn($a) => $a->where('user_id', $user->id));
            });
            return;
        }

        if ($ownership === 'user_or_module') {
            $query->where(function ($q) use ($accessibleIds, $user) {
                $q->where('user_id', $user->id);
                if ($accessibleIds !== null && !empty($accessibleIds)) {
                    $q->orWhereIn('module_id', $accessibleIds);
                }
            });
            return;
        }
    }

    public static function filters(): array
    {
        return [
            self::FILTER_ALL => 'All',
            self::FILTER_SERVICES => 'Services',
            self::FILTER_ASSETS => 'Assets',
            self::FILTER_TASKS => 'Tasks',
            self::FILTER_VAULT => 'Vault',
            self::FILTER_USERS => 'Users',
        ];
    }
}
