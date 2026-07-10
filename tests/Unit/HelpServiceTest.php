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

    public function test_get_role_slug_returns_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $this->assertEquals('super-admin', $this->service->getRoleSlug($user));
    }

    public function test_get_role_slug_returns_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'admin')->firstOrFail());
        $this->assertEquals('admin', $this->service->getRoleSlug($user));
    }

    public function test_get_role_slug_returns_read_only_for_null(): void
    {
        $this->assertEquals('read-only', $this->service->getRoleSlug(null));
    }

    public function test_get_role_guide_file_returns_correct_file(): void
    {
        $this->assertEquals('02_SUPER_ADMIN_GUIDE.md', $this->service->getRoleGuideFile('super-admin'));
        $this->assertEquals('03_ADMIN_GUIDE.md', $this->service->getRoleGuideFile('admin'));
        $this->assertEquals('04_IT_STAFF_GUIDE.md', $this->service->getRoleGuideFile('it-support'));
        $this->assertEquals('05_READ_ONLY_GUIDE.md', $this->service->getRoleGuideFile('read-only'));
    }

    public function test_get_role_guide_file_returns_null_for_unknown(): void
    {
        $this->assertNull($this->service->getRoleGuideFile('nonexistent'));
    }

    public function test_get_role_guide_file_for_user_returns_correct_file(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $this->assertEquals('02_SUPER_ADMIN_GUIDE.md', $this->service->getRoleGuideFileForUser($user));
    }

    public function test_get_role_guide_file_for_user_returns_read_only_for_null(): void
    {
        $this->assertEquals('05_READ_ONLY_GUIDE.md', $this->service->getRoleGuideFileForUser(null));
    }

    public function test_get_role_label_returns_correct_labels(): void
    {
        $this->assertEquals('Super Administrator', $this->service->getRoleLabel('super-admin'));
        $this->assertEquals('Administrator', $this->service->getRoleLabel('admin'));
        $this->assertEquals('IT Staff', $this->service->getRoleLabel('it-support'));
        $this->assertEquals('Read Only User', $this->service->getRoleLabel('read-only'));
        $this->assertEquals('User', $this->service->getRoleLabel('unknown'));
    }

    public function test_load_markdown_file_returns_content_for_existing_file(): void
    {
        $content = $this->service->loadMarkdownFile('01_QUICK_START_GUIDE.md');
        $this->assertNotNull($content);
        $this->assertStringContainsString('#', $content);
    }

    public function test_load_markdown_file_returns_null_for_missing_file(): void
    {
        $this->assertNull($this->service->loadMarkdownFile('nonexistent.md'));
    }

    public function test_render_markdown_file_returns_html(): void
    {
        $html = $this->service->renderMarkdownFile('01_QUICK_START_GUIDE.md');
        $this->assertNotNull($html);
        $this->assertStringContainsString('<h1', $html);
        $this->assertStringContainsString('</h1>', $html);
    }

    public function test_render_markdown_file_returns_null_for_missing(): void
    {
        $this->assertNull($this->service->renderMarkdownFile('nonexistent.md'));
    }

    public function test_get_today_workflow_returns_super_admin_tasks(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $workflow = $this->service->getTodayWorkflow($user);
        $this->assertCount(5, $workflow);
        $this->assertEquals('Review failed logins', $workflow[0]['label']);
    }

    public function test_get_today_workflow_returns_admin_tasks(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'admin')->firstOrFail());
        $workflow = $this->service->getTodayWorkflow($user);
        $this->assertCount(5, $workflow);
        $this->assertEquals('Check dashboard summary', $workflow[0]['label']);
    }

    public function test_get_today_workflow_returns_read_only_tasks(): void
    {
        $user = User::factory()->create();
        $workflow = $this->service->getTodayWorkflow($user);
        $this->assertCount(4, $workflow);
        $this->assertEquals('Review Dashboard', $workflow[0]['label']);
    }

    public function test_get_quick_links_returns_super_admin_links(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $links = $this->service->getQuickLinks($user);
        $this->assertCount(5, $links);
        $this->assertEquals('Users', $links[0]['label']);
    }

    public function test_get_quick_links_returns_admin_links(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'admin')->firstOrFail());
        $links = $this->service->getQuickLinks($user);
        $this->assertCount(5, $links);
        $this->assertEquals('Domains', $links[0]['label']);
    }

    public function test_get_module_help_returns_html_for_known_module(): void
    {
        $html = $this->service->getModuleHelp('dashboard');
        $this->assertNotNull($html);
        $this->assertStringContainsString('<h1', $html);
    }

    public function test_get_module_help_returns_null_for_unknown_module(): void
    {
        $this->assertNull($this->service->getModuleHelp('nonexistent'));
    }

    public function test_get_help_sidebar_links_returns_expected_structure(): void
    {
        $links = $this->service->getHelpSidebarLinks();
        $this->assertArrayHasKey('getting-started', $links);
        $this->assertArrayHasKey('daily-ops', $links);
        $this->assertEquals('Getting Started', $links['getting-started']['label']);
    }

    public function test_search_returns_empty_for_short_query(): void
    {
        $this->assertEmpty($this->service->search('x'));
    }

    public function test_search_returns_results_for_valid_query(): void
    {
        $results = $this->service->search('dashboard');
        $this->assertNotEmpty($results);
        foreach ($results as $result) {
            $this->assertArrayHasKey('file', $result);
            $this->assertArrayHasKey('label', $result);
            $this->assertArrayHasKey('snippet', $result);
        }
    }

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
        $this->assertFalse($this->service->isDeveloperDoc('getting-started'));
    }

    public function test_get_all_doc_labels_returns_expected_count(): void
    {
        $labels = $this->service->getAllDocLabels();
        $this->assertCount(19, $labels);
        $this->assertEquals('Getting Started', $labels['01_QUICK_START_GUIDE.md']);
    }
}
