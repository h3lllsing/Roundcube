<?php

namespace App\Services;

use App\Helpers\MarkdownHelper;

class HelpService
{
    private string $docsPath;
    private array $registry;

    private array $roleDocMap;

    private array $developerDocMap;

    public function __construct()
    {
        $this->docsPath = base_path();
        $this->registry = config('help-center', []);

        $this->roleDocMap = [
            'super-admin' => 'super-admin-guide',
            'admin'       => 'quick-start',
            'it-support'  => 'quick-start',
            'read-only'   => 'quick-start',
        ];

        $this->developerDocMap = [
            'architecture'      => 'docs/reference/architecture/01_SYSTEM_OVERVIEW.md',
            'developer-rbac'    => 'docs/reference/architecture/05_PERMISSION_SYSTEM.md',
            'disaster-recovery' => 'docs/reference/guides/DEPLOYMENT_GUIDE.md',
        ];
    }

    // ── Registry Access ───────────────────────────────────────────────

    public function getRegistry(): array
    {
        return $this->registry;
    }

    public function getDocument(string $slug): ?array
    {
        return $this->registry['documents'][$slug] ?? null;
    }

    public function documentExists(string $slug): bool
    {
        return isset($this->registry['documents'][$slug]);
    }

    public function getLegacySlugRedirects(): array
    {
        return $this->registry['legacy_slugs'] ?? [];
    }

    public function isRetiredSlug(string $slug): bool
    {
        return in_array($slug, $this->registry['retired_slugs'] ?? [], true);
    }

    public function getModuleDocMap(): array
    {
        return $this->registry['module_doc_map'] ?? [];
    }

    // ── Document Visibility ───────────────────────────────────────────

    public function canAccess(string $slug, $user): bool
    {
        $doc = $this->getDocument($slug);
        if (!$doc) {
            return false;
        }

        $audience = $doc['audience'] ?? 'all';

        if ($audience === 'all') {
            return true;
        }

        if ($audience === 'super-admin') {
            return $user && $user->hasRole('super-admin');
        }

        return false;
    }

    // ── Document Loading ──────────────────────────────────────────────

    public function loadMarkdownFile(string $filename): ?string
    {
        $path = $this->docsPath . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($path)) {
            return null;
        }
        return file_get_contents($path);
    }

    public function renderMarkdownFile(string $filename): ?string
    {
        $md = $this->loadMarkdownFile($filename);
        if ($md === null) {
            return null;
        }
        return MarkdownHelper::toHtml($md);
    }

    public function loadRegisteredDocument(string $slug): ?string
    {
        $doc = $this->getDocument($slug);
        if (!$doc) {
            return null;
        }
        return $this->loadMarkdownFile($doc['file']);
    }

    public function getDocumentContent(string $slug): ?string
    {
        $md = $this->loadRegisteredDocument($slug);
        if ($md === null) {
            return null;
        }
        return MarkdownHelper::toHtml($md);
    }

    // ── Role Resolution ───────────────────────────────────────────────

    public function getRoleSlug($user): string
    {
        if (!$user) {
            return 'read-only';
        }
        if ($user->hasRole('super-admin')) {
            return 'super-admin';
        }
        if ($user->hasRole('admin')) {
            return 'admin';
        }
        if ($user->hasRole('it-support')) {
            return 'it-support';
        }
        return 'read-only';
    }

    public function getRoleGuideFile(string $roleSlug): ?string
    {
        $docSlug = $this->roleDocMap[$roleSlug] ?? null;
        if (!$docSlug) {
            return null;
        }
        $doc = $this->getDocument($docSlug);
        return $doc ? $doc['file'] : null;
    }

    public function getRoleGuideFileForUser($user): ?string
    {
        return $this->getRoleGuideFile($this->getRoleSlug($user));
    }

    public function getRoleLabel(string $roleSlug): string
    {
        return match ($roleSlug) {
            'super-admin' => 'Super Administrator',
            'admin' => 'Administrator',
            'it-support' => 'IT Support',
            'read-only' => 'Read-Only User',
            default => 'User',
        };
    }

    // ── Navigation ────────────────────────────────────────────────────

    public function getNavigation($user): array
    {
        $categories = $this->registry['categories'] ?? [];
        $documents = $this->registry['documents'] ?? [];

        $nav = [];

        foreach ($categories as $catKey => $cat) {
            $catAudience = $cat['audience'] ?? 'all';
            if ($catAudience === 'super-admin' && (!$user || !$user->hasRole('super-admin'))) {
                continue;
            }

            $docs = [];
            foreach ($documents as $slug => $doc) {
                if ($doc['category'] !== $catKey) {
                    continue;
                }
                if (!$this->canAccess($slug, $user)) {
                    continue;
                }
                $docs[] = [
                    'slug' => $slug,
                    'title' => $doc['title'],
                    'weight' => $doc['weight'] ?? 0,
                ];
            }

            if (empty($docs)) {
                continue;
            }

            usort($docs, fn($a, $b) => $a['weight'] - $b['weight']);

            $nav[$catKey] = [
                'label' => $cat['label'],
                'weight' => $cat['weight'],
                'documents' => $docs,
            ];
        }

        uasort($nav, fn($a, $b) => $a['weight'] - $b['weight']);

        return $nav;
    }

    // ── Contextual Help ───────────────────────────────────────────────

    public function getModuleHelp(string $moduleSlug): ?string
    {
        $moduleDocMap = $this->getModuleDocMap();
        $docSlug = $moduleDocMap[$moduleSlug] ?? null;

        if (!$docSlug) {
            return null;
        }

        $user = auth()->user();
        if (!$this->canAccess($docSlug, $user)) {
            return null;
        }

        return $this->getDocumentContent($docSlug);
    }

    // ── Search ────────────────────────────────────────────────────────

    public function search(string $query, $user): array
    {
        $query = strtolower(trim($query));
        if (strlen($query) < 2) {
            return [];
        }

        $results = [];
        $documents = $this->registry['documents'] ?? [];

        foreach ($documents as $slug => $doc) {
            if (!($doc['searchable'] ?? false)) {
                continue;
            }
            if (!$this->canAccess($slug, $user)) {
                continue;
            }

            $path = $this->docsPath . DIRECTORY_SEPARATOR . $doc['file'];
            if (!file_exists($path)) {
                continue;
            }

            $content = file_get_contents($path);
            $lines = explode("\n", $content);
            $headingMatches = [];

            foreach ($lines as $i => $line) {
                $lowerLine = strtolower($line);
                if (!str_contains($lowerLine, $query)) {
                    continue;
                }

                $heading = '';
                for ($j = $i - 1; $j >= max(0, $i - 10); $j--) {
                    if (preg_match('/^#{1,3}\s+(.+)$/', $lines[$j], $h)) {
                        $heading = $h[1];
                        break;
                    }
                }

                $snippet = trim(mb_substr(strip_tags($line), 0, 120));
                if (empty($snippet)) {
                    continue;
                }

                $key = $slug . ':' . $i;
                $headingMatches[$key] = [
                    'slug' => $slug,
                    'title' => $doc['title'],
                    'heading' => $heading ?: $doc['title'],
                    'snippet' => $snippet,
                    'line' => $i + 1,
                    'score' => str_starts_with($lowerLine, '#') ? 10 : (str_contains($lowerLine, '## ' . $query) ? 8 : 5),
                ];
            }

            $results = array_merge($results, array_values($headingMatches));
        }

        usort($results, fn($a, $b) => $b['score'] - $a['score']);
        return array_slice($results, 0, 20);
    }

    // ── Developer Docs (separate from production registry) ────────────

    public function getDeveloperDocFile(string $slug): ?string
    {
        return $this->developerDocMap[$slug] ?? null;
    }

    public function isDeveloperDoc(string $slug): bool
    {
        return isset($this->developerDocMap[$slug]);
    }

    // ── Legacy Labels (kept for backward compat, not used in new flow) ──

    public function getAllDocLabels(): array
    {
        $labels = [];
        foreach ($this->registry['documents'] ?? [] as $slug => $doc) {
            $labels[$doc['file']] = $doc['title'];
        }
        return $labels;
    }

    // ── Today Workflow & Quick Links (unchanged) ──────────────────────

    public function getTodayWorkflow($user): array
    {
        $slug = $this->getRoleSlug($user);
        return match ($slug) {
            'super-admin' => [
                ['label' => 'Review failed logins', 'route' => 'login-audits.index', 'done' => false],
                ['label' => 'Check upcoming renewals', 'route' => 'expiry-trackers.index', 'done' => false],
                ['label' => 'Review activity log', 'route' => 'activity-logs.index', 'done' => false],
                ['label' => 'Check user notifications', 'route' => 'notifications.index', 'done' => false],
                ['label' => 'Review dashboard', 'route' => 'dashboard', 'done' => false],
            ],
            'admin' => [
                ['label' => 'Check dashboard summary', 'route' => 'dashboard', 'done' => false],
                ['label' => 'Review open tasks', 'route' => 'tasks.index', 'done' => false],
                ['label' => 'Check renewals', 'route' => 'expiry-trackers.index', 'done' => false],
                ['label' => 'Review notifications', 'route' => 'notifications.index', 'done' => false],
                ['label' => 'Update service records', 'route' => 'domains.index', 'done' => false],
            ],
            'it-support' => [
                ['label' => 'Open My Tasks', 'route' => 'tasks.my', 'done' => false],
                ['label' => 'Search for client records', 'route' => 'search', 'done' => false],
                ['label' => 'Update service records', 'route' => 'domains.index', 'done' => false],
                ['label' => 'Add notes to completed work', 'route' => 'notes.index', 'done' => false],
                ['label' => 'Complete pending tasks', 'route' => 'tasks.my', 'done' => false],
            ],
            default => [
                ['label' => 'Review Dashboard', 'route' => 'dashboard', 'done' => false],
                ['label' => 'Search client records', 'route' => 'search', 'done' => false],
                ['label' => 'Review Calendar', 'route' => 'calendar', 'done' => false],
                ['label' => 'Check notifications', 'route' => 'notifications.index', 'done' => false],
            ],
        };
    }

    public function getQuickLinks($user): array
    {
        $slug = $this->getRoleSlug($user);
        return match ($slug) {
            'super-admin' => [
                ['label' => 'Users', 'route' => 'users.index', 'icon' => 'users'],
                ['label' => 'Roles', 'route' => 'roles.index', 'icon' => 'shield'],
                ['label' => 'Hosting', 'route' => 'hostings.index', 'icon' => 'server'],
                ['label' => 'Domains', 'route' => 'domains.index', 'icon' => 'globe'],
                ['label' => 'Tasks', 'route' => 'tasks.index', 'icon' => 'checklist'],
            ],
            'admin' => [
                ['label' => 'Domains', 'route' => 'domains.index', 'icon' => 'globe'],
                ['label' => 'Hosting', 'route' => 'hostings.index', 'icon' => 'server'],
                ['label' => 'Tasks', 'route' => 'tasks.index', 'icon' => 'checklist'],
                ['label' => 'Renewals', 'route' => 'expiry-trackers.index', 'icon' => 'clock'],
                ['label' => 'Vault', 'route' => 'vault.index', 'icon' => 'lock'],
            ],
            'it-support' => [
                ['label' => 'My Tasks', 'route' => 'tasks.my', 'icon' => 'checklist'],
                ['label' => 'Search', 'route' => 'search', 'icon' => 'search'],
                ['label' => 'Hosting', 'route' => 'hostings.index', 'icon' => 'server'],
                ['label' => 'Domains', 'route' => 'domains.index', 'icon' => 'globe'],
                ['label' => 'Service Providers', 'route' => 'service-providers.index', 'icon' => 'briefcase'],
            ],
            default => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
                ['label' => 'Search', 'route' => 'search', 'icon' => 'search'],
                ['label' => 'Calendar', 'route' => 'calendar', 'icon' => 'calendar'],
                ['label' => 'Tasks', 'route' => 'tasks.index', 'icon' => 'checklist'],
            ],
        };
    }
}
