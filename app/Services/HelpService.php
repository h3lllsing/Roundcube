<?php

namespace App\Services;

use App\Helpers\MarkdownHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HelpService
{
    private string $docsPath;
    private array $roleGuideMap;
    private array $moduleHelpMap;
    private array $helpSidebarLinks;
    private array $developerDocMap;

    public function __construct()
    {
        $this->docsPath = base_path();

        $this->developerDocMap = [
            'architecture'      => '17_ARCHITECTURE_OVERVIEW.md',
            'developer-rbac'    => '18_DEVELOPER_RBAC_REFERENCE.md',
            'disaster-recovery' => '16_DISASTER_RECOVERY_GUIDE.md',
        ];

        $this->roleGuideMap = [
            'super-admin' => '02_SUPER_ADMIN_GUIDE.md',
            'admin'       => '03_ADMIN_GUIDE.md',
            'it-support'  => '04_IT_STAFF_GUIDE.md',
            'read-only'   => '05_READ_ONLY_GUIDE.md',
        ];

        $this->moduleHelpMap = [
            'dashboard'         => ['file' => '01_QUICK_START_GUIDE.md'],
            'domains'           => ['file' => '03_ADMIN_GUIDE.md'],
            'hostings'          => ['file' => '03_ADMIN_GUIDE.md'],
            'service-providers' => ['file' => '03_ADMIN_GUIDE.md'],
            'vps'               => ['file' => '03_ADMIN_GUIDE.md'],
            'domain-emails'     => ['file' => '03_ADMIN_GUIDE.md'],
            'voip'              => ['file' => '03_ADMIN_GUIDE.md'],
            'other-services'    => ['file' => '03_ADMIN_GUIDE.md'],
            'expiry-trackers'   => ['file' => '03_ADMIN_GUIDE.md'],
            'assets'            => ['file' => '03_ADMIN_GUIDE.md'],
            'tasks'             => ['file' => '03_ADMIN_GUIDE.md'],
            'vault'             => ['file' => '03_ADMIN_GUIDE.md'],
            'notes'             => ['file' => '03_ADMIN_GUIDE.md'],
            'users'             => ['file' => '02_SUPER_ADMIN_GUIDE.md'],
            'roles'             => ['file' => '02_SUPER_ADMIN_GUIDE.md'],
            'module-permissions'=> ['file' => '02_SUPER_ADMIN_GUIDE.md'],
            'activity-logs'     => ['file' => '02_SUPER_ADMIN_GUIDE.md'],
            'smtp-profiles'     => ['file' => '02_SUPER_ADMIN_GUIDE.md'],
            'reports'           => ['file' => '02_SUPER_ADMIN_GUIDE.md'],
            'notifications'     => ['file' => '01_QUICK_START_GUIDE.md'],
            'monitoring'        => ['file' => '19_MONITORING_GUIDE.md'],
            'calendar'          => ['file' => '06_DAILY_OPERATIONS_GUIDE.md'],
            'role-templates'    => ['file' => '02_SUPER_ADMIN_GUIDE.md'],
            'privileges'        => ['file' => '02_SUPER_ADMIN_GUIDE.md'],
            'modules'           => ['file' => '02_SUPER_ADMIN_GUIDE.md'],
            'features'          => ['file' => '02_SUPER_ADMIN_GUIDE.md'],
            'import'            => ['file' => '02_SUPER_ADMIN_GUIDE.md'],
            'attachments'       => ['file' => '02_SUPER_ADMIN_GUIDE.md'],
            'webhooks'          => ['file' => '02_SUPER_ADMIN_GUIDE.md'],
            'tokens'            => ['file' => '02_SUPER_ADMIN_GUIDE.md'],
            'login-audits'      => ['file' => '02_SUPER_ADMIN_GUIDE.md'],
            'search'            => ['file' => '01_QUICK_START_GUIDE.md'],
            'export'            => ['file' => '03_ADMIN_GUIDE.md'],
            'profile'           => ['file' => '01_QUICK_START_GUIDE.md'],
            'my-permissions'    => ['file' => '08_PERMISSION_REFERENCE.md'],
        ];

        $this->helpSidebarLinks = [
            'getting-started' => ['label' => 'Getting Started', 'file' => '01_QUICK_START_GUIDE.md', 'roles' => null],
            'my-role-guide'   => ['label' => 'My Role Guide', 'file' => null, 'roles' => null, 'dynamic' => true],
            'daily-ops'       => ['label' => 'Daily Operations', 'file' => '06_DAILY_OPERATIONS_GUIDE.md', 'roles' => null],
            'monitoring'      => ['label' => 'Monitoring', 'file' => '19_MONITORING_GUIDE.md', 'roles' => null],
            'workflows'       => ['label' => 'Workflows', 'file' => '10_WORKFLOW_GUIDE.md', 'roles' => null],
            'permission-guide'=> ['label' => 'Permission Guide', 'file' => '08_PERMISSION_REFERENCE.md', 'roles' => null],
            'faq'             => ['label' => 'FAQ', 'file' => '07_FAQ.md', 'roles' => null],
            'troubleshooting' => ['label' => 'Troubleshooting', 'file' => '12_TROUBLESHOOTING_GUIDE.md', 'roles' => null],
            'release-notes'   => ['label' => 'Release Notes', 'file' => '14_RELEASE_NOTES_v1.0.md', 'roles' => null],
        ];
    }

    public function getRoleSlug($user): string
    {
        if (!$user) return 'read-only';
        if ($user->hasRole('super-admin')) return 'super-admin';
        if ($user->hasRole('admin')) return 'admin';
        if ($user->hasRole('it-support')) return 'it-support';
        return 'read-only';
    }

    public function getRoleGuideFile(string $roleSlug): ?string
    {
        return $this->roleGuideMap[$roleSlug] ?? null;
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
            'it-support' => 'IT Staff',
            'read-only' => 'Read Only User',
            default => 'User',
        };
    }

    public function loadMarkdownFile(string $filename): ?string
    {
        $path = $this->docsPath . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($path)) return null;
        return file_get_contents($path);
    }

    public function renderMarkdownFile(string $filename): ?string
    {
        $md = $this->loadMarkdownFile($filename);
        if ($md === null) return null;
        return MarkdownHelper::toHtml($md);
    }

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

    public function getModuleHelp(string $moduleSlug): ?string
    {
        $info = $this->moduleHelpMap[$moduleSlug] ?? null;
        if (!$info) return null;
        return $this->renderMarkdownFile($info['file']);
    }

    public function getHelpSidebarLinks(): array
    {
        return $this->helpSidebarLinks;
    }

    public function search(string $query): array
    {
        $query = strtolower(trim($query));
        if (strlen($query) < 2) return [];

        $results = [];
        $files = [
            '01_QUICK_START_GUIDE.md' => 'Getting Started',
            '02_SUPER_ADMIN_GUIDE.md' => 'Super Admin Guide',
            '03_ADMIN_GUIDE.md' => 'Admin Guide',
            '04_IT_STAFF_GUIDE.md' => 'IT Staff Guide',
            '05_READ_ONLY_GUIDE.md' => 'Read Only Guide',
            '06_DAILY_OPERATIONS_GUIDE.md' => 'Daily Operations',
            '07_FAQ.md' => 'FAQ',
            '08_PERMISSION_REFERENCE.md' => 'Permission Reference',
            '09_ROLE_MATRIX.md' => 'Role Matrix',
            '10_WORKFLOW_GUIDE.md' => 'Workflow Guide',
            '19_MONITORING_GUIDE.md' => 'Monitoring Guide',
        ];

        foreach ($files as $filename => $label) {
            $path = $this->docsPath . DIRECTORY_SEPARATOR . $filename;
            if (!file_exists($path)) continue;

            $content = file_get_contents($path);
            $lines = explode("\n", $content);
            $headingMatches = [];

            foreach ($lines as $i => $line) {
                $lowerLine = strtolower($line);
                if (str_contains($lowerLine, $query)) {
                    $heading = '';
                    for ($j = $i - 1; $j >= max(0, $i - 10); $j--) {
                        if (preg_match('/^#{1,3}\s+(.+)$/', $lines[$j], $h)) {
                            $heading = $h[1];
                            break;
                        }
                    }
                    $snippet = trim(mb_substr(strip_tags($line), 0, 120));
                    if (!empty($snippet)) {
                        $key = $filename . ':' . $i;
                        $headingMatches[$key] = [
                            'file' => $filename,
                            'label' => $label,
                            'heading' => $heading ?: $label,
                            'snippet' => $snippet,
                            'line' => $i + 1,
                            'score' => str_starts_with($lowerLine, '#') ? 10 : (str_contains($lowerLine, '## ' . $query) ? 8 : 5),
                        ];
                    }
                }
            }

            $results = array_merge($results, array_values($headingMatches));
        }

        usort($results, fn($a, $b) => $b['score'] - $a['score']);
        return array_slice($results, 0, 20);
    }

    public function getDeveloperDocFile(string $slug): ?string
    {
        return $this->developerDocMap[$slug] ?? null;
    }

    public function isDeveloperDoc(string $slug): bool
    {
        return isset($this->developerDocMap[$slug]);
    }

    public function getAllDocLabels(): array
    {
        return [
            '01_QUICK_START_GUIDE.md' => 'Getting Started',
            '02_SUPER_ADMIN_GUIDE.md' => 'Super Admin Guide',
            '03_ADMIN_GUIDE.md' => 'Admin Guide',
            '04_IT_STAFF_GUIDE.md' => 'IT Staff Guide',
            '05_READ_ONLY_GUIDE.md' => 'Read Only Guide',
            '06_DAILY_OPERATIONS_GUIDE.md' => 'Daily Operations',
            '07_FAQ.md' => 'Problem Resolution Guide',
            '08_PERMISSION_REFERENCE.md' => 'Permission Reference',
            '09_ROLE_MATRIX.md' => 'Role Matrix',
            '10_WORKFLOW_GUIDE.md' => 'Workflow Guide',
            '11_GLOSSARY.md' => 'Glossary',
            '12_TROUBLESHOOTING_GUIDE.md' => 'Troubleshooting Guide',
            '13_BACKUP_AND_RESTORE.md' => 'Backup and Restore',
            '14_RELEASE_NOTES_v1.0.md' => 'Release Notes',
            '15_VERSION_HISTORY.md' => 'Version History',
            '16_DISASTER_RECOVERY_GUIDE.md' => 'Disaster Recovery',
            '17_ARCHITECTURE_OVERVIEW.md' => 'Architecture Overview',
            '18_DEVELOPER_RBAC_REFERENCE.md' => 'Developer RBAC Reference',
            '19_MONITORING_GUIDE.md' => 'Monitoring Guide',
        ];
    }
}
