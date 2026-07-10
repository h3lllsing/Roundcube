<?php

namespace App\Http\Controllers;

use App\Helpers\MarkdownHelper;
use App\Services\HelpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HelpController extends Controller
{
    public function __construct(
        private HelpService $helpService
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $roleSlug = $this->helpService->getRoleSlug($user);
        $guideFile = $this->helpService->getRoleGuideFileForUser($user);

        $guideHtml = null;
        $guideToc = [];
        if ($guideFile) {
            $md = $this->helpService->loadMarkdownFile($guideFile);
            if ($md) {
                $guideHtml = self::wrapSections(MarkdownHelper::toHtml($md));
                $guideToc = MarkdownHelper::extractHeadings($md);
            }
        }

        $roleLabel = $this->helpService->getRoleLabel($roleSlug);
        $todayWorkflow = $this->helpService->getTodayWorkflow($user);
        $quickLinks = $this->helpService->getQuickLinks($user);
        $sidebarLinks = $this->helpService->getHelpSidebarLinks();
        $showDeveloperDocs = $user && $user->hasRole('super-admin');

        return view('help.index', compact(
            'roleSlug', 'roleLabel', 'guideHtml', 'guideToc', 'todayWorkflow',
            'quickLinks', 'sidebarLinks', 'showDeveloperDocs'
        ));
    }

    public function show(string $slug): JsonResponse
    {
        $user = auth()->user();
        $file = null;

        if ($slug === 'my-role-guide') {
            $file = $this->helpService->getRoleGuideFileForUser($user);
        } elseif (in_array($slug, ['super-admin-guide', 'admin-guide', 'it-support-guide', 'read-only-guide', 'monitoring-guide'])) {
            $map = [
                'super-admin-guide' => '02_SUPER_ADMIN_GUIDE.md',
                'admin-guide' => '03_ADMIN_GUIDE.md',
                'it-support-guide' => '04_IT_STAFF_GUIDE.md',
                'read-only-guide' => '05_READ_ONLY_GUIDE.md',
                'monitoring-guide' => '19_MONITORING_GUIDE.md',
            ];
            $file = $map[$slug];
        } elseif ($slug === 'about') {
            $file = '15_VERSION_HISTORY.md';
        } elseif ($this->helpService->isDeveloperDoc($slug)) {
            if (!$user || !$user->hasRole('super-admin')) {
                return response()->json(['error' => 'Not authorized'], 403);
            }
            $file = $this->helpService->getDeveloperDocFile($slug);
        } else {
            $sidebarLinks = $this->helpService->getHelpSidebarLinks();
            if (isset($sidebarLinks[$slug])) {
                $file = $sidebarLinks[$slug]['file'] ?? null;
            }
            if (!$file) {
                $allDocs = $this->helpService->getAllDocLabels();
                foreach ([$slug . '.md', strtoupper(str_replace('-', '_', $slug)) . '.md'] as $c) {
                    if (isset($allDocs[$c])) { $file = $c; break; }
                }
            }
        }

        if (!$file || !file_exists(base_path($file))) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        $md = $this->helpService->loadMarkdownFile($file);
        $html = $md ? self::wrapSections(MarkdownHelper::toHtml($md)) : null;
        $toc = $md ? MarkdownHelper::extractHeadings($md) : [];
        $label = $this->getLabelForFile($file);

        return response()->json([
            'html' => $html,
            'title' => $label,
            'file' => $file,
            'toc' => $toc,
        ]);
    }

    public function moduleHelp(string $module): JsonResponse
    {
        $user = auth()->user();
        $moduleSlug = str_replace('_', '-', $module);
        $html = $this->helpService->getModuleHelp($moduleSlug);
        if (!$html) {
            $guideFile = $this->helpService->getRoleGuideFileForUser($user);
            $md = $guideFile ? $this->helpService->loadMarkdownFile($guideFile) : null;
            $html = $md ? MarkdownHelper::toHtml($md) : null;
        }
        return response()->json([
            'html' => $html ?: '<p class="text-gray-500">Help content not available for this module.</p>',
            'title' => ucwords(str_replace('-', ' ', $moduleSlug)) . ' Help',
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'nullable|string|max:500']);
        $query = $request->input('q', '');
        $results = $this->helpService->search($query);

        $sidebarLinks = $this->helpService->getHelpSidebarLinks();
        $fileToSlug = [];
        foreach ($sidebarLinks as $slug => $link) {
            if (isset($link['file'])) {
                $fileToSlug[$link['file']] = $slug;
            }
        }
        $fallback = [
            '02_SUPER_ADMIN_GUIDE.md' => 'super-admin-guide',
            '03_ADMIN_GUIDE.md' => 'admin-guide',
            '04_IT_STAFF_GUIDE.md' => 'it-support-guide',
            '05_READ_ONLY_GUIDE.md' => 'read-only-guide',
            '19_MONITORING_GUIDE.md' => 'monitoring-guide',
        ];
        foreach ($fallback as $f => $s) {
            if (!isset($fileToSlug[$f])) $fileToSlug[$f] = $s;
        }

        foreach ($results as &$r) {
            $r['slug'] = $fileToSlug[$r['file']] ?? strtolower(
                preg_replace('/^\d+_/', '', pathinfo($r['file'], PATHINFO_FILENAME))
            );
        }

        return response()->json(['results' => $results]);
    }

    private function getLabelForFile(string $file): string
    {
        $labels = $this->helpService->getAllDocLabels();
        return $labels[$file] ?? ucwords(str_replace(['_', '.md', '-'], [' ', '', ' '], $file));
    }

    public static function wrapSections(string $html): string
    {
        $keywords = 'Purpose|When to Use|Permission Required|Step-by-Step Workflow|Best Practices|Common Mistakes|Typical Business Scenario|Expected Result|Available Templates|What You Cannot Do';
        $lines = explode("\n", $html);
        $result = [];
        $inCard = false;
        $pattern = '/<h3 id="([^"]*)"[^>]*>\s*(' . $keywords . ')\s*<\/h3>/i';
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $m)) {
                if ($inCard) {
                    $result[] = '</div>';
                }
                $result[] = '<div class="help-section-card bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl p-5 mb-4">';
                $inCard = true;
                $line = '<h3 class="help-section-heading text-sm font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-3" id="' . $m[1] . '">' . $m[2] . '</h3>';
            }
            $result[] = $line;
        }
        if ($inCard) {
            $result[] = '</div>';
        }
        return implode("\n", $result);
    }
}
