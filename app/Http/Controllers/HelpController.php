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
        $navigation = $this->helpService->getNavigation($user);
        $showDeveloperDocs = $user && $user->hasRole('super-admin');

        return view('help.index', compact(
            'roleSlug', 'roleLabel', 'guideHtml', 'guideToc', 'todayWorkflow',
            'quickLinks', 'navigation', 'showDeveloperDocs'
        ));
    }

    public function show(string $slug): JsonResponse
    {
        $user = auth()->user();

        $legacySlugs = $this->helpService->getLegacySlugRedirects();
        if (isset($legacySlugs[$slug])) {
            return response()->json(['redirect' => $legacySlugs[$slug]]);
        }

        if ($this->helpService->isRetiredSlug($slug)) {
            return response()->json(['error' => 'This document has been retired and is no longer available.'], 410);
        }

        if ($this->helpService->isDeveloperDoc($slug)) {
            if (!$user || !$user->hasRole('super-admin')) {
                return response()->json(['error' => 'Not authorized'], 403);
            }
            $file = $this->helpService->getDeveloperDocFile($slug);
            $md = $file ? $this->helpService->loadMarkdownFile($file) : null;
            if (!$md) {
                return response()->json(['error' => 'Document not found'], 404);
            }
            $html = self::wrapSections(MarkdownHelper::toHtml($md));
            $toc = MarkdownHelper::extractHeadings($md);
            return response()->json([
                'html' => $html,
                'title' => ucwords(str_replace('-', ' ', $slug)),
                'slug' => $slug,
                'toc' => $toc,
            ]);
        }

        if (!$this->helpService->documentExists($slug)) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        if (!$this->helpService->canAccess($slug, $user)) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $md = $this->helpService->loadRegisteredDocument($slug);
        if (!$md) {
            return response()->json(['error' => 'Document file not found'], 404);
        }

        $html = self::wrapSections(MarkdownHelper::toHtml($md));
        $toc = MarkdownHelper::extractHeadings($md);
        $doc = $this->helpService->getDocument($slug);

        return response()->json([
            'html' => $html,
            'title' => $doc['title'] ?? ucwords(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'toc' => $toc,
        ]);
    }

    public function moduleHelp(string $module): JsonResponse
    {
        $moduleSlug = str_replace('_', '-', $module);
        $html = $this->helpService->getModuleHelp($moduleSlug);

        return response()->json([
            'html' => $html ?: '<p class="text-gray-500">Help content not available for this module.</p>',
            'title' => ucwords(str_replace('-', ' ', $moduleSlug)) . ' Help',
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'nullable|string|max:500']);
        $query = $request->input('q', '');
        $user = auth()->user();
        $results = $this->helpService->search($query, $user);

        return response()->json(['results' => $results]);
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
