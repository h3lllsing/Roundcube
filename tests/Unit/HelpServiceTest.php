<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\HelpService;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpServiceTest extends TestCase
{
    use RefreshDatabase;

    private HelpService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->service = app(HelpService::class);
    }

    // ── HELPERS ──────────────────────────────────────────────────────

    private function createUserWithRole(string $roleSlug): User
    {
        $user = User::factory()->create();
        $role = Role::where('slug', $roleSlug)->firstOrFail();
        $user->assignRole($role);
        return $user;
    }

    // ── REGISTRY INTEGRITY ──────────────────────────────────────────

    public function test_registered_slugs_are_unique(): void
    {
        $documents = $this->service->getRegistry()['documents'] ?? [];
        $slugs = array_keys($documents);
        $this->assertSame($slugs, array_unique($slugs), 'Duplicate slugs found in document registry');
    }

    public function test_registered_document_files_exist(): void
    {
        $documents = $this->service->getRegistry()['documents'] ?? [];
        foreach ($documents as $slug => $doc) {
            $this->assertFileExists(
                base_path($doc['file']),
                "Document file for '{$slug}' does not exist: {$doc['file']}"
            );
        }
    }

    public function test_no_registered_document_points_to_legacy_root_file(): void
    {
        $documents = $this->service->getRegistry()['documents'] ?? [];
        foreach ($documents as $slug => $doc) {
            $this->assertStringNotContainsString(
                '01_QUICK_START_GUIDE.md',
                $doc['file'],
                "Document '{$slug}' still references legacy root file"
            );
            $this->assertStringNotContainsString(
                '02_SUPER_ADMIN_GUIDE.md',
                $doc['file'],
                "Document '{$slug}' still references legacy root file"
            );
            $this->assertMatchesRegularExpression(
                '/^help\//',
                $doc['file'],
                "Document '{$slug}' is not in the help/ directory"
            );
        }
    }

    public function test_no_internal_developer_documents_in_production_registry(): void
    {
        $documents = $this->service->getRegistry()['documents'] ?? [];
        $internalSlugs = ['architecture', 'developer-rbac', 'disaster-recovery'];
        foreach ($documents as $slug => $doc) {
            $this->assertNotContains($slug, $internalSlugs, "Internal developer slug '{$slug}' found in production registry");
        }
    }

    public function test_module_doc_map_targets_valid_registered_slugs(): void
    {
        $moduleDocMap = $this->service->getModuleDocMap();
        $documents = $this->service->getRegistry()['documents'] ?? [];
        foreach ($moduleDocMap as $module => $docSlug) {
            $this->assertArrayHasKey(
                $docSlug,
                $documents,
                "Module '{$module}' maps to '{$docSlug}' which is not a registered document"
            );
        }
    }

    public function test_retired_slugs_are_not_in_documents(): void
    {
        $retired = $this->service->getRegistry()['retired_slugs'] ?? [];
        $documents = $this->service->getRegistry()['documents'] ?? [];
        foreach ($retired as $slug) {
            $this->assertArrayNotHasKey($slug, $documents, "Retired slug '{$slug}' is still registered as a document");
        }
    }

    public function test_legacy_slugs_map_to_valid_document_slugs(): void
    {
        $legacySlugs = $this->service->getLegacySlugRedirects();
        $documents = $this->service->getRegistry()['documents'] ?? [];
        foreach ($legacySlugs as $old => $new) {
            $this->assertArrayHasKey(
                $new,
                $documents,
                "Legacy slug '{$old}' redirects to '{$new}' which is not a registered document"
            );
        }
    }

    // ── ROLE RESOLUTION (preserved from old tests) ──────────────────

    public function test_get_role_slug_returns_super_admin(): void
    {
        $user = $this->createUserWithRole('super-admin');
        $this->assertEquals('super-admin', $this->service->getRoleSlug($user));
    }

    public function test_get_role_slug_returns_admin(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->assertEquals('admin', $this->service->getRoleSlug($user));
    }

    public function test_get_role_slug_returns_read_only_for_null(): void
    {
        $this->assertEquals('read-only', $this->service->getRoleSlug(null));
    }

    public function test_get_role_label_uses_correct_terminology(): void
    {
        $this->assertEquals('IT Support', $this->service->getRoleLabel('it-support'));
        $this->assertEquals('Read-Only User', $this->service->getRoleLabel('read-only'));
    }

    // ── AUTHORIZATION ──────────────────────────────────────────────

    public function test_all_audience_document_accessible_by_normal_user(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->assertTrue($this->service->canAccess('quick-start', $user));
    }

    public function test_super_admin_document_accessible_by_super_admin(): void
    {
        $user = $this->createUserWithRole('super-admin');
        $this->assertTrue($this->service->canAccess('super-admin-guide', $user));
    }

    public function test_super_admin_document_denied_to_normal_user(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->assertFalse($this->service->canAccess('super-admin-guide', $user));
    }

    public function test_unknown_slug_returns_false_for_can_access(): void
    {
        $user = $this->createUserWithRole('super-admin');
        $this->assertFalse($this->service->canAccess('nonexistent-document', $user));
    }

    // ── NAVIGATION ─────────────────────────────────────────────────

    public function test_navigation_excludes_sa_docs_for_normal_user(): void
    {
        $user = $this->createUserWithRole('admin');
        $nav = $this->service->getNavigation($user);

        $foundSaSection = false;
        foreach ($nav as $catKey => $category) {
            foreach ($category['documents'] as $doc) {
                if ($doc['slug'] === 'super-admin-guide') {
                    $foundSaSection = true;
                }
            }
        }
        $this->assertFalse($foundSaSection, 'Super Admin guide should not appear in navigation for normal user');
    }

    public function test_navigation_includes_sa_docs_for_super_admin(): void
    {
        $user = $this->createUserWithRole('super-admin');
        $nav = $this->service->getNavigation($user);

        $found = false;
        foreach ($nav as $catKey => $category) {
            foreach ($category['documents'] as $doc) {
                if ($doc['slug'] === 'super-admin-guide') {
                    $found = true;
                }
            }
        }
        $this->assertTrue($found, 'Super Admin guide should appear in navigation for Super Admin');
    }

    // ── LEGACY SLUGS ───────────────────────────────────────────────

    public function test_legacy_slug_redirect_maps_to_valid_target(): void
    {
        $legacySlugs = $this->service->getLegacySlugRedirects();

        $this->assertArrayHasKey('getting-started', $legacySlugs);
        $this->assertEquals('quick-start', $legacySlugs['getting-started']);

        $this->assertArrayHasKey('permission-guide', $legacySlugs);
        $this->assertEquals('permission-reference', $legacySlugs['permission-guide']);

        $this->assertArrayHasKey('monitoring-guide', $legacySlugs);
        $this->assertEquals('monitoring', $legacySlugs['monitoring-guide']);

        $this->assertArrayHasKey('about', $legacySlugs);
        $this->assertEquals('changelog', $legacySlugs['about']);
    }

    public function test_retired_slug_returns_true_from_is_retired(): void
    {
        $this->assertTrue($this->service->isRetiredSlug('my-role-guide'));
        $this->assertTrue($this->service->isRetiredSlug('daily-ops'));
        $this->assertTrue($this->service->isRetiredSlug('faq'));
        $this->assertTrue($this->service->isRetiredSlug('admin-guide'));
        $this->assertTrue($this->service->isRetiredSlug('it-support-guide'));
        $this->assertTrue($this->service->isRetiredSlug('read-only-guide'));
    }

    public function test_active_slug_is_not_retired(): void
    {
        $this->assertFalse($this->service->isRetiredSlug('quick-start'));
        $this->assertFalse($this->service->isRetiredSlug('dashboard'));
        $this->assertFalse($this->service->isRetiredSlug('monitoring'));
    }

    public function test_developer_slugs_are_not_exposed_as_retired(): void
    {
        $this->assertFalse($this->service->isRetiredSlug('architecture'));
        $this->assertFalse($this->service->isRetiredSlug('developer-rbac'));
    }

    // ── SEARCH ─────────────────────────────────────────────────────

    public function test_search_returns_empty_for_short_query(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->assertEmpty($this->service->search('x', $user));
    }

    public function test_search_returns_results_for_valid_query(): void
    {
        $user = $this->createUserWithRole('admin');
        $results = $this->service->search('dashboard', $user);
        $this->assertNotEmpty($results);
        foreach ($results as $result) {
            $this->assertArrayHasKey('slug', $result);
            $this->assertArrayHasKey('title', $result);
            $this->assertArrayHasKey('snippet', $result);
        }
    }

    public function test_search_uses_registry_slugs_not_filenames(): void
    {
        $user = $this->createUserWithRole('admin');
        $results = $this->service->search('dashboard', $user);
        if (!empty($results)) {
            foreach ($results as $result) {
                $this->assertIsString($result['slug']);
                $this->assertStringEndsNotWith('.md', $result['slug']);
            }
        }
    }

    public function test_search_excludes_sa_docs_for_normal_user(): void
    {
        $user = $this->createUserWithRole('admin');
        $results = $this->service->search('Super Admin', $user);
        foreach ($results as $result) {
            $this->assertNotEquals('super-admin-guide', $result['slug']);
        }
    }

    public function test_search_includes_sa_docs_for_super_admin(): void
    {
        $user = $this->createUserWithRole('super-admin');
        $results = $this->service->search('Super Admin', $user);
        $found = false;
        foreach ($results as $result) {
            if ($result['slug'] === 'super-admin-guide') {
                $found = true;
            }
        }
        $this->assertTrue($found, 'Super Admin guide should be searchable by Super Admin');
    }

    // ── CONTEXTUAL HELP ────────────────────────────────────────────

    public function test_module_help_resolves_to_same_source_as_full_help(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->actingAs($user);

        $moduleHtml = $this->service->getModuleHelp('domains');
        $docHtml = $this->service->getDocumentContent('domains');

        $this->assertNotNull($moduleHtml);
        $this->assertEquals($docHtml, $moduleHtml);
    }

    public function test_unknown_module_returns_null(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->actingAs($user);

        $this->assertNull($this->service->getModuleHelp('nonexistent-module'));
    }

    public function test_module_help_enforces_visibility(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->actingAs($user);

        $this->assertNull($this->service->getModuleHelp('users'));
    }

    public function test_module_help_no_legacy_role_guide_fallback(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->actingAs($user);

        $this->assertNull($this->service->getModuleHelp('unknown'));
    }

    // ── DEVELOPER DOCS (kept separate from registry) ───────────────

    public function test_get_developer_doc_file_returns_correct_file(): void
    {
        $this->assertEquals('17_ARCHITECTURE_OVERVIEW.md', $this->service->getDeveloperDocFile('architecture'));
        $this->assertEquals('18_DEVELOPER_RBAC_REFERENCE.md', $this->service->getDeveloperDocFile('developer-rbac'));
        $this->assertNull($this->service->getDeveloperDocFile('nonexistent'));
    }

    public function test_is_developer_doc_returns_true_for_developer_slugs(): void
    {
        $this->assertTrue($this->service->isDeveloperDoc('architecture'));
        $this->assertTrue($this->service->isDeveloperDoc('developer-rbac'));
        $this->assertFalse($this->service->isDeveloperDoc('quick-start'));
    }

    // ── DOCUMENT LOADING ───────────────────────────────────────────

    public function test_load_registered_document_returns_markdown(): void
    {
        $md = $this->service->loadRegisteredDocument('quick-start');
        $this->assertNotNull($md);
        $this->assertStringContainsString('#', $md);
    }

    public function test_load_registered_document_returns_null_for_unknown_slug(): void
    {
        $this->assertNull($this->service->loadRegisteredDocument('nonexistent'));
    }

    public function test_get_document_content_returns_html(): void
    {
        $html = $this->service->getDocumentContent('quick-start');
        $this->assertNotNull($html);
        $this->assertStringContainsString('<h1', $html);
    }

    public function test_get_document_content_returns_null_for_unknown(): void
    {
        $this->assertNull($this->service->getDocumentContent('nonexistent'));
    }

    // ── TODAY WORKFLOW & QUICK LINKS (preserved) ───────────────────

    public function test_get_today_workflow_returns_super_admin_tasks(): void
    {
        $user = $this->createUserWithRole('super-admin');
        $workflow = $this->service->getTodayWorkflow($user);
        $this->assertCount(5, $workflow);
        $this->assertEquals('Review failed logins', $workflow[0]['label']);
    }

    public function test_get_quick_links_returns_expected_structure(): void
    {
        $user = $this->createUserWithRole('admin');
        $links = $this->service->getQuickLinks($user);
        $this->assertCount(5, $links);
        $this->assertEquals('Domains', $links[0]['label']);
    }
}
