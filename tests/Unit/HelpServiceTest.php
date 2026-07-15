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
        $this->assertTrue($this->service->isRetiredSlug('admin-guide'));
        $this->assertTrue($this->service->isRetiredSlug('it-support-guide'));
        $this->assertTrue($this->service->isRetiredSlug('read-only-guide'));
    }

    public function test_active_slug_is_not_retired(): void
    {
        $this->assertFalse($this->service->isRetiredSlug('quick-start'));
        $this->assertFalse($this->service->isRetiredSlug('dashboard'));
        $this->assertFalse($this->service->isRetiredSlug('monitoring'));
        $this->assertFalse($this->service->isRetiredSlug('faq'));
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
        $this->assertEquals('docs/reference/architecture/01_SYSTEM_OVERVIEW.md', $this->service->getDeveloperDocFile('architecture'));
        $this->assertEquals('docs/reference/architecture/05_PERMISSION_SYSTEM.md', $this->service->getDeveloperDocFile('developer-rbac'));
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

    // ── BATCH 2: CORE DOCUMENTATION INTEGRITY ──────────────────────

    public function test_batch2_docs_are_registered(): void
    {
        $slugs = ['understanding-permissions', 'my-permissions', 'credential-reveal'];
        foreach ($slugs as $slug) {
            $this->assertNotNull(
                $this->service->getDocument($slug),
                "Batch 2 doc '{$slug}' is not registered"
            );
        }
    }

    public function test_batch2_doc_files_exist(): void
    {
        $batch2 = [
            'understanding-permissions' => 'help/getting-started/understanding-permissions.md',
            'my-permissions' => 'help/getting-started/my-permissions.md',
            'credential-reveal' => 'help/reference/credential-reveal.md',
        ];
        foreach ($batch2 as $slug => $file) {
            $this->assertFileExists(
                base_path($file),
                "Batch 2 file for '{$slug}' does not exist: {$file}"
            );
        }
    }

    public function test_batch2_docs_are_searchable(): void
    {
        $batch2 = ['understanding-permissions', 'my-permissions', 'credential-reveal'];
        $user = $this->createUserWithRole('admin');
        foreach ($batch2 as $slug) {
            $doc = $this->service->getDocument($slug);
            $this->assertNotNull($doc, "Batch 2 doc '{$slug}' not found in registry");
            $this->assertTrue(
                $doc['searchable'] ?? false,
                "Batch 2 doc '{$slug}' should be searchable"
            );
        }
    }

    public function test_batch2_docs_have_correct_audience(): void
    {
        $user = $this->createUserWithRole('admin');

        $allAudience = ['understanding-permissions', 'my-permissions', 'credential-reveal'];
        foreach ($allAudience as $slug) {
            $this->assertTrue(
                $this->service->canAccess($slug, $user),
                "Batch 2 doc '{$slug}' should be accessible to normal user"
            );
        }
    }

    public function test_batch2_docs_have_no_legacy_root_dependency(): void
    {
        $batch2Files = [
            'help/getting-started/understanding-permissions.md',
            'help/getting-started/my-permissions.md',
            'help/reference/credential-reveal.md',
        ];
        foreach ($batch2Files as $file) {
            $this->assertStringStartsWith(
                'help/',
                $file,
                "File '{$file}' is not in the help/ directory"
            );
            $this->assertDoesNotMatchRegularExpression(
                '/^\d+_/',
                basename($file),
                "File '{$file}' has legacy numeric prefix"
            );
        }
    }

    public function test_redirect_destinations_remain_valid(): void
    {
        $legacySlugs = $this->service->getLegacySlugRedirects();
        $documents = $this->service->getRegistry()['documents'] ?? [];
        foreach ($legacySlugs as $old => $new) {
            $this->assertArrayHasKey(
                $new,
                $documents,
                "Legacy slug '{$old}' redirects to '{$new}' which is not registered"
            );
            $this->assertFileExists(
                base_path($documents[$new]['file']),
                "Redirect target '{$new}' file does not exist"
            );
        }
    }

    public function test_batch2_docs_contain_no_old_permission_terminology(): void
    {
        $batch2Files = [
            'help/getting-started/understanding-permissions.md',
            'help/getting-started/my-permissions.md',
            'help/reference/permission-reference.md',
            'help/reference/credential-reveal.md',
        ];

        $forbiddenPatterns = [
            '/[Rr]eveal.*(?:is a|as a).*(?:standalone permission|assignable control)/',
            '/[Dd]elete.*(?:is a|as a).*(?:normal|standard|assignable).*(?:control|permission)/',
        ];

        foreach ($batch2Files as $file) {
            $path = base_path($file);
            if (!file_exists($path)) {
                continue;
            }
            $content = file_get_contents($path);
            foreach ($forbiddenPatterns as $pattern) {
                $this->assertDoesNotMatchRegularExpression(
                    $pattern,
                    $content,
                    "File '{$file}' contains old permission terminology matching: {$pattern}"
                );
            }
        }
    }

    public function test_batch2_updated_docs_load_and_render(): void
    {
        $slugs = ['quick-start', 'dashboard', 'permission-reference',
                  'understanding-permissions', 'my-permissions', 'credential-reveal'];
        $user = $this->createUserWithRole('admin');
        $this->actingAs($user);

        foreach ($slugs as $slug) {
            $html = $this->service->getDocumentContent($slug);
            $this->assertNotNull(
                $html,
                "Batch 2 doc '{$slug}' returned null content"
            );
            $this->assertStringContainsString(
                '<h1',
                $html,
                "Batch 2 doc '{$slug}' has no <h1> tag"
            );
        }
    }

    // ── BATCH 3 — All remaining portal docs ─────────────────────────

    public function test_batch3_docs_are_registered(): void
    {
        $slugs = [
            'vps', 'voip', 'service-providers', 'domain-emails', 'other-services',
            'expiry-trackers', 'assets', 'g-mails', 'vault', 'tasks', 'notes',
            'calendar', 'global-search', 'users', 'roles', 'module-permissions',
            'privileges', 'role-templates', 'activity-logs', 'login-audits',
            'features', 'modules', 'import-export', 'bulk-actions', 'reports',
            'webhooks', 'smtp-profiles', 'api-tokens', 'attachments', 'faq',
        ];
        foreach ($slugs as $slug) {
            $this->assertNotNull(
                $this->service->getDocument($slug),
                "Batch 3 doc '{$slug}' is not registered"
            );
        }
    }

    public function test_batch3_doc_files_exist(): void
    {
        $batch3 = [
            'vps'               => 'help/portal-reference/infrastructure/vps.md',
            'voip'              => 'help/portal-reference/infrastructure/voip.md',
            'service-providers' => 'help/portal-reference/infrastructure/service-providers.md',
            'domain-emails'     => 'help/portal-reference/infrastructure/domain-emails.md',
            'other-services'    => 'help/portal-reference/infrastructure/other-services.md',
            'expiry-trackers'   => 'help/portal-reference/infrastructure/expiry-trackers.md',
            'assets'            => 'help/portal-reference/infrastructure/assets.md',
            'g-mails'           => 'help/portal-reference/infrastructure/g-mails.md',
            'vault'             => 'help/portal-reference/credentials/vault.md',
            'tasks'             => 'help/portal-reference/operations/tasks.md',
            'notes'             => 'help/portal-reference/operations/notes.md',
            'calendar'          => 'help/portal-reference/operations/calendar.md',
            'global-search'     => 'help/portal-reference/operations/global-search.md',
            'users'             => 'help/portal-reference/administrator/users.md',
            'roles'             => 'help/portal-reference/administrator/roles.md',
            'module-permissions'=> 'help/portal-reference/administrator/module-permissions.md',
            'privileges'        => 'help/portal-reference/administrator/privileges.md',
            'role-templates'    => 'help/portal-reference/administrator/role-templates.md',
            'activity-logs'     => 'help/portal-reference/administrator/activity-logs.md',
            'login-audits'      => 'help/portal-reference/administrator/login-audits.md',
            'features'          => 'help/portal-reference/administrator/features.md',
            'modules'           => 'help/portal-reference/administrator/modules.md',
            'import-export'     => 'help/portal-reference/administrator/import-export.md',
            'bulk-actions'      => 'help/portal-reference/administrator/bulk-actions.md',
            'reports'           => 'help/portal-reference/administrator/reports.md',
            'webhooks'          => 'help/portal-reference/administrator/webhooks.md',
            'smtp-profiles'     => 'help/portal-reference/administrator/smtp-profiles.md',
            'api-tokens'        => 'help/portal-reference/administrator/api-tokens.md',
            'attachments'       => 'help/portal-reference/administrator/attachments.md',
            'faq'               => 'help/faq.md',
        ];
        foreach ($batch3 as $slug => $file) {
            $this->assertFileExists(
                base_path($file),
                "Batch 3 file for '{$slug}' does not exist: {$file}"
            );
        }
    }

    public function test_batch3_all_audience_docs_are_searchable(): void
    {
        $allAudienceSlugs = [
            'vps', 'voip', 'service-providers', 'domain-emails', 'other-services',
            'expiry-trackers', 'assets', 'g-mails', 'vault', 'tasks', 'notes',
            'calendar', 'global-search', 'faq',
        ];
        foreach ($allAudienceSlugs as $slug) {
            $doc = $this->service->getDocument($slug);
            $this->assertNotNull($doc, "Slug '{$slug}' not found");
            $this->assertTrue(
                $doc['searchable'] ?? false,
                "Batch 3 '{$slug}' should be searchable"
            );
        }
    }

    public function test_batch3_all_audience_docs_accessible_by_normal_user(): void
    {
        $user = $this->createUserWithRole('admin');
        $slugs = [
            'vps', 'voip', 'service-providers', 'domain-emails', 'other-services',
            'expiry-trackers', 'assets', 'g-mails', 'vault', 'tasks', 'notes',
            'calendar', 'global-search', 'faq',
        ];
        foreach ($slugs as $slug) {
            $this->assertTrue(
                $this->service->canAccess($slug, $user),
                "Batch 3 all-audience doc '{$slug}' denied to normal user"
            );
        }
    }

    public function test_batch3_admin_docs_denied_to_normal_user(): void
    {
        $user = $this->createUserWithRole('admin');
        $slugs = [
            'users', 'roles', 'module-permissions', 'privileges', 'role-templates',
            'activity-logs', 'login-audits', 'features', 'modules', 'import-export',
            'bulk-actions', 'reports', 'webhooks', 'smtp-profiles', 'api-tokens',
            'attachments',
        ];
        foreach ($slugs as $slug) {
            $this->assertFalse(
                $this->service->canAccess($slug, $user),
                "Batch 3 SA doc '{$slug}' should be denied to normal user"
            );
        }
    }

    public function test_batch3_admin_docs_accessible_by_super_admin(): void
    {
        $user = $this->createUserWithRole('super-admin');
        $slugs = [
            'users', 'roles', 'module-permissions', 'privileges', 'role-templates',
            'activity-logs', 'login-audits', 'features', 'modules', 'import-export',
            'bulk-actions', 'reports', 'webhooks', 'smtp-profiles', 'api-tokens',
            'attachments',
        ];
        foreach ($slugs as $slug) {
            $this->assertTrue(
                $this->service->canAccess($slug, $user),
                "Batch 3 SA doc '{$slug}' denied to super admin"
            );
        }
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
