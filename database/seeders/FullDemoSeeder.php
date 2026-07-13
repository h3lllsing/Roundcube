<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\ExpiryTrackerNotification;
use App\Models\GMail;
use App\Models\Hosting;
use App\Models\LoginAudit;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\Note;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\SmtpProfile;
use App\Models\Task;
use App\Models\User;
use App\Models\UserModulePermission;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use App\Models\Webhook;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FullDemoSeeder extends Seeder
{
    private array $userIds = [];
    private array $moduleCache = [];
    private array $providerIds = [];
    private string $demoPassword = 'password';

    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new \RuntimeException('FullDemoSeeder: APP_ENV must be "local". Aborting.');
        }
        $db = config('database.connections.mysql.database');
        if ($db !== 'tyro_project') {
            throw new \RuntimeException("FullDemoSeeder: DB_DATABASE is '$db', expected 'tyro_project'. Aborting.");
        }

        $this->command?->info('FullDemoSeeder: safety checks passed. Seeding...');

        $this->loadModuleCache();
        $this->createAssetLocations();
        $this->createUsers();
        $this->createServiceProviders();
        $this->createSmtpProfiles();
        $this->createHostings();
        $this->createVpsRecords();
        $this->createDomains();
        $this->createDomainEmails();
        $this->createVoipRecords();
        $this->createOtherServices();
        $this->createExpiryTrackers();
        $this->createExpiryTrackerNotifications();
        $this->createAssets();
        $this->createGMails();
        $this->createVaultEntries();
        $this->createTasks();
        $this->createNotes();
        $this->createWebhooks();
        $this->createLoginAudits();
        $this->createUserModulePermissions();
        $this->createApiTokens();

        $this->command?->info('FullDemoSeeder: complete.');
    }

    private function getMod(string $slug): ?Module
    {
        return $this->moduleCache[$slug] ?? null;
    }

    private function loadModuleCache(): void
    {
        Module::all()->each(fn ($m) => $this->moduleCache[$m->slug] = $m);
    }

    private function pickUserId(): int
    {
        return $this->userIds[array_rand($this->userIds)];
    }

    private function pickUserIdExcept(int $exclude): int
    {
        $pool = array_values(array_filter($this->userIds, fn ($id) => $id !== $exclude));
        return $pool[array_rand($pool)];
    }

    private function pickProviderId(): int
    {
        return $this->providerIds[array_rand($this->providerIds)];
    }

    private function randomDate(string $start, string $end): string
    {
        return date('Y-m-d', mt_rand(strtotime($start), strtotime($end)));
    }

    private function pickStatus(array $statuses): string
    {
        return $statuses[array_rand($statuses)];
    }

    private function createAssetLocations(): void
    {
        $locations = ['Main Office', 'Server Room', 'Warehouse', 'Remote Office', 'Data Center'];
        foreach ($locations as $i => $name) {
            \App\Models\AssetLocation::updateOrCreate(
                ['name' => $name],
                ['description' => 'Demo location: ' . $name, 'is_active' => true]
            );
        }
        $this->command?->info('  Asset Locations: ' . count($locations));
    }

    private function createUsers(): void
    {
        $users = [
            ['name' => 'Tyro Admin', 'email' => 'admin@tyro.project', 'role' => 'super-admin'],
            ['name' => 'Alice Johnson', 'email' => 'alice@example.test', 'role' => 'admin'],
            ['name' => 'Bob Smith', 'email' => 'bob@example.test', 'role' => 'editor'],
            ['name' => 'Charlie Brown', 'email' => 'charlie@example.test', 'role' => 'user'],
            ['name' => 'Diana Prince', 'email' => 'diana@example.test', 'role' => 'customer'],
            ['name' => 'Eve Martinez', 'email' => 'eve@example.test', 'role' => 'user'],
            ['name' => 'Frank Castle', 'email' => 'frank@example.test', 'role' => 'admin'],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                ['name' => $u['name'], 'password' => bcrypt($this->demoPassword), 'email_verified_at' => now()]
            );
            $roleModel = \App\Models\Role::where('slug', $u['role'])->first();
            if ($roleModel && ! $user->roles->contains($roleModel->id)) {
                $user->roles()->attach($roleModel);
            }
            $this->userIds[] = $user->id;
        }

        $this->command?->info('  Users: ' . count($this->userIds));
    }

    private function createServiceProviders(): void
    {
        $data = [
            ['name' => 'DigitalOcean Demo', 'type' => 'vps', 'provider' => 'DigitalOcean', 'website' => 'https://demo-digitalocean.example.test'],
            ['name' => 'Namecheap Demo', 'type' => 'domain', 'provider' => 'Namecheap Inc.', 'website' => 'https://demo-namecheap.example.test'],
            ['name' => 'AWS Demo', 'type' => 'vps', 'provider' => 'Amazon Web Services', 'website' => 'https://demo-aws.example.test'],
            ['name' => 'Linode Demo', 'type' => 'vps', 'provider' => 'Akamai / Linode', 'website' => 'https://demo-linode.example.test'],
            ['name' => 'GoDaddy Demo', 'type' => 'domain', 'provider' => 'GoDaddy LLC', 'website' => 'https://demo-godaddy.example.test'],
            ['name' => 'Cloudflare Demo', 'type' => 'domain', 'provider' => 'Cloudflare Inc.', 'website' => 'https://demo-cloudflare.example.test'],
            ['name' => 'cPanel Demo', 'type' => 'hosting', 'provider' => 'cPanel LLC', 'website' => 'https://demo-cpanel.example.test'],
            ['name' => 'SiteGround Demo', 'type' => 'hosting', 'provider' => 'SiteGround', 'website' => 'https://demo-siteground.example.test'],
            ['name' => 'Twilio Demo', 'type' => 'telecom', 'provider' => 'Twilio Inc.', 'website' => 'https://demo-twilio.example.test'],
            ['name' => 'Slack Demo', 'type' => 'other', 'provider' => 'Slack Technologies', 'website' => 'https://demo-slack.example.test'],
            ['name' => 'GitHub Demo', 'type' => 'other', 'provider' => 'GitHub Inc.', 'website' => 'https://demo-github.example.test'],
            ['name' => 'Google Workspace Demo', 'type' => 'email', 'provider' => 'Google LLC', 'website' => 'https://demo-workspace.example.test'],
        ];

        $mod = $this->getMod('service-providers');
        foreach ($data as $d) {
            $sp = ServiceProvider::firstOrCreate(
                ['name' => $d['name']],
                [
                    'user_id' => $this->userIds[0],
                    'module_id' => $mod?->id,
                    'type' => $d['type'],
                    'provider' => $d['provider'],
                    'website' => $d['website'],
                    'login_id' => 'demo-' . Str::slug($d['name']),
                    'password' => $this->demoPassword,
                    'email' => 'billing@' . Str::slug($d['name'], '.') . '.example.test',
                    'cost' => round(mt_rand(500, 50000) / 100, 2),
                    'start_date' => $this->randomDate('-2 years', '-1 month'),
                    'expiry_date' => $this->randomDate('-1 month', '+1 year'),
                    'status' => $this->pickStatus(['active', 'active', 'active', 'expired', 'cancelled']),
                    'description' => 'Demo ' . $d['name'] . ' account for local testing.',
                ]
            );
            $this->providerIds[] = $sp->id;
        }
        $this->command?->info('  Service Providers: ' . count($this->providerIds));
    }

    private function createSmtpProfiles(): void
    {
        $data = [
            ['name' => 'Mailtrap Demo', 'sender_name' => 'OpsPilot Demo', 'sender_email' => 'demo@example.test', 'smtp_host' => 'sandbox.smtp.mailtrap.io', 'smtp_port' => 587, 'smtp_encryption' => 'tls', 'smtp_username' => 'demo@example.test', 'is_default' => true],
            ['name' => 'SendGrid Demo', 'sender_name' => 'OpsPilot Alerts', 'sender_email' => 'alerts@example.test', 'smtp_host' => 'smtp.sendgrid.net', 'smtp_port' => 587, 'smtp_encryption' => 'tls', 'smtp_username' => 'apikey', 'is_default' => false],
            ['name' => 'Local SMTP', 'sender_name' => 'Local Dev', 'sender_email' => 'dev@localhost.test', 'smtp_host' => '127.0.0.1', 'smtp_port' => 1025, 'smtp_encryption' => null, 'smtp_username' => 'dev', 'is_default' => false],
        ];

        foreach ($data as $d) {
            SmtpProfile::updateOrCreate(
                ['name' => $d['name']],
                [
                    'sender_name' => $d['sender_name'],
                    'sender_email' => $d['sender_email'],
                    'reply_to_email' => $d['sender_email'],
                    'smtp_host' => $d['smtp_host'],
                    'smtp_port' => $d['smtp_port'],
                    'smtp_encryption' => $d['smtp_encryption'],
                    'smtp_username' => $d['smtp_username'],
                    'smtp_password' => $this->demoPassword,
                    'is_default' => $d['is_default'],
                    'is_active' => true,
                    'priority' => 100,
                    'created_by' => $this->userIds[0],
                ]
            );
        }
        $this->command?->info('  SMTP Profiles: ' . count($data));
    }

    private function createHostings(): void
    {
        $hostingNames = [
            'Main Business Site', 'Client Portal', 'E-Commerce Store', 'Blog Platform',
            'Knowledge Base', 'Support Desk', 'Marketing Landing', 'API Gateway',
            'Staging Environment', 'Dev Server', 'QA Testing', 'Admin Dashboard',
            'Analytics Platform', 'File Server', 'Backup Storage', 'Email Server',
            'DNS Secondary', 'CDN Origin', 'Database Server', 'Redis Cache',
            'Monitoring Stack', 'CI/CD Runner', 'Container Registry', 'Artifact Storage',
            'Document Portal', 'HR System', 'CRM Instance', 'ERP System',
            'Learning Platform', 'Forum Hosting', 'Wiki Server', 'Media Server',
        ];

        $mod = $this->getMod('hostings');
        $plans = ['Basic', 'Business', 'Deluxe', 'Premium', 'Enterprise'];
        $statuses = ['active', 'active', 'active', 'active', 'inactive', 'expired', 'suspended', 'cancelled', 'pending_transfer'];

        $created = 0;
        foreach ($hostingNames as $name) {
            $userId = $this->pickUserId();
            $plan = $plans[array_rand($plans)];
            $slug = Str::slug($name);
            $startDate = $this->randomDate('-3 years', '-1 month');
            $expiryDate = date('Y-m-d', strtotime($startDate . ' + ' . mt_rand(1, 24) . ' months'));

            Hosting::create([
                'user_id' => $userId,
                'module_id' => $mod?->id,
                'service_provider_id' => $this->pickProviderId(),
                'name' => $name . ' Hosting',
                'username' => 'admin_' . $slug,
                'password' => $this->demoPassword,
                'cpanel_url' => 'https://cpanel.' . $slug . '.example.test:2083',
                'plan' => $plan,
                'domain' => $slug . '.example.test',
                'domain_ip' => '192.0.2.' . mt_rand(10, 200),
                'mail_domain_ip' => '203.0.113.' . mt_rand(10, 200),
                'cpanel_ip' => '198.51.100.' . mt_rand(10, 200),
                'start_date' => $startDate,
                'expiry_date' => $expiryDate,
                'billing_period_months' => mt_rand(1, 3) === 1 ? 1 : 12,
                'cost' => round(mt_rand(500, 30000) / 100, 2),
                'status' => $this->pickStatus($statuses),
                'description' => 'Demo hosting account for ' . $name,
                'monitoring_url' => 'https://' . $slug . '.example.test/health',
                'last_ping_at' => mt_rand(0, 1) ? now()->subMinutes(mt_rand(1, 1440)) : null,
            ]);
            $created++;
        }
        $this->command?->info("  Hosting: $created");
    }

    private function createVpsRecords(): void
    {
        $names = [
            'Web Server 01', 'Web Server 02', 'App Server 01', 'DB Master',
            'DB Replica 01', 'DB Replica 02', 'Redis Cluster', 'Load Balancer',
            'Cache Layer', 'Worker Node 01', 'Worker Node 02', 'Monitoring Node',
            'Log Aggregator', 'Search Index', 'Queue Consumer', 'CI/CD Agent',
            'Staging Web', 'Staging DB', 'Dev Environment', 'QA Server',
            'VPN Gateway', 'Firewall Appliance', 'DNS Resolver', 'Backup Node',
            'Container Host', 'Test Runner', 'Mail Gateway',
        ];

        $mod = $this->getMod('vps');
        $plans = ['s-1vcpu-1gb', 's-2vcpu-2gb', 's-4vcpu-8gb', 'c-2vcpu-4gb', 'g6-2vcpu-8gb', 'm-4vcpu-16gb', 's-8vcpu-16gb'];
        $oses = ['Ubuntu 22.04', 'Ubuntu 24.04', 'Debian 12', 'CentOS 9', 'AlmaLinux 9', 'Rocky Linux 9', 'Fedora 40'];
        $locations = ['US-EAST', 'US-WEST', 'EU-WEST', 'EU-CENTRAL', 'AP-SOUTHEAST', 'AP-NORTHEAST', 'US-CENTRAL'];
        $departments = ['Engineering', 'DevOps', 'Infrastructure', 'Security', 'QA', 'Staging'];
        $statuses = ['active', 'active', 'active', 'active', 'expired', 'cancelled'];

        $created = 0;
        foreach ($names as $name) {
            $created++;
            $startDate = $this->randomDate('-2 years', '-1 month');
            $expiryDate = date('Y-m-d', strtotime($startDate . ' + ' . mt_rand(1, 24) . ' months'));

            Vps::create([
                'user_id' => $this->pickUserId(),
                'module_id' => $mod?->id,
                'service_provider_id' => $this->pickProviderId(),
                'name' => $name,
                'plan' => $plans[array_rand($plans)],
                'ip_address' => '203.0.113.' . mt_rand(1, 250),
                'password' => $this->demoPassword,
                'os' => $oses[array_rand($oses)],
                'ram_mb' => [1024, 2048, 4096, 8192, 16384, 32768][array_rand([0, 1, 2, 3, 4, 5])],
                'disk_gb' => [25, 50, 80, 160, 320, 640][array_rand([0, 1, 2, 3, 4, 5])],
                'cpu_cores' => [1, 2, 4, 8, 16][array_rand([0, 1, 2, 3, 4])],
                'department' => $departments[array_rand($departments)],
                'location' => $locations[array_rand($locations)],
                'login_ids' => ['root', 'deploy', 'admin'],
                'additional_ips' => mt_rand(0, 1) ? ['198.51.100.' . mt_rand(1, 250)] : null,
                'billing_period_months' => 12,
                'cost' => round(mt_rand(500, 30000) / 100, 2),
                'start_date' => $startDate,
                'expiry_date' => $expiryDate,
                'status' => $this->pickStatus($statuses),
                'description' => 'Demo VPS: ' . $name,
                'monitoring_url' => 'https://monitor.example.test/vps/' . Str::slug($name),
                'last_ping_at' => mt_rand(0, 1) ? now()->subMinutes(mt_rand(1, 1440)) : null,
            ]);
        }
        $this->command?->info("  VPS: $created");
    }

    private function createDomains(): void
    {
        $names = [
            'example.test', 'demo-portal.test', 'shop-test.test', 'blog-dev.test',
            'api-staging.test', 'admin-demo.test', 'docs-local.test', 'cdn-origin.test',
            'mail-server.test', 'analytics.test', 'backup.test', 'monitor.test',
            'wiki-demo.test', 'forum-demo.test', 'hr-portal.test', 'crm-demo.test',
            'erp-test.test', 'lms-demo.test', 'git-demo.test', 'ci-demo.test',
            'artifacts.test', 'media-cdn.test', 'status-demo.test', 'help-demo.test',
            'community.test', 'learn-demo.test', 'assets-cdn.test', 'events.test',
            'forms-demo.test', 'survey-demo.test', 'workers.test', 'stream-demo.test',
        ];

        $mod = $this->getMod('domains');
        $statuses = ['active', 'active', 'active', 'active', 'expired', 'pending_transfer', 'cancelled'];
        $cfStatuses = ['active', 'active', 'active', 'pending', 'none', null];

        $created = 0;
        $domainIds = [];
        foreach ($names as $name) {
            $created++;
            $userId = $this->pickUserId();
            $startDate = $this->randomDate('-5 years', '-1 month');
            $expiryDate = date('Y-m-d', strtotime($startDate . ' + ' . mt_rand(1, 36) . ' months'));

            $domain = Domain::create([
                'user_id' => $userId,
                'module_id' => $mod?->id,
                'hosting_id' => null,
                'service_provider_id' => $this->pickProviderId(),
                'name' => $name,
                'registration_date' => $startDate,
                'expiry_date' => $expiryDate,
                'auto_renew' => (bool) mt_rand(0, 1),
                'billing_period_months' => 12,
                'cost' => round(mt_rand(800, 30000) / 100, 2),
                'status' => $this->pickStatus($statuses),
                'cloudflare_status' => $cfStatuses[array_rand($cfStatuses)],
                'dns_servers' => ['ns1.dns.example.test', 'ns2.dns.example.test'],
                'description' => 'Demo domain: ' . $name,
                'monitoring_url' => 'https://' . $name . '/health',
                'last_ping_at' => mt_rand(0, 1) ? now()->subMinutes(mt_rand(1, 1440)) : null,
            ]);
            $domainIds[] = $domain->id;
        }

        // Link some domains to hostings
        $hostings = Hosting::inRandomOrder()->take(15)->get();
        $allDomainIds = Domain::pluck('id')->toArray();
        foreach ($hostings as $h) {
            if (! empty($allDomainIds)) {
                $did = $allDomainIds[array_rand($allDomainIds)];
                Domain::where('id', $did)->update(['hosting_id' => $h->id]);
            }
        }

        $this->command?->info("  Domains: $created");
    }

    private function createDomainEmails(): void
    {
        $mod = $this->getMod('domain-emails');
        $domainIds = Domain::pluck('id')->toArray();
        $statuses = ['active', 'active', 'active', 'expired', 'cancelled'];

        $prefixes = ['info', 'support', 'admin', 'billing', 'sales', 'contact', 'noreply', 'hello', 'team', 'dev', 'ops', 'hr', 'jobs', 'help', 'feedback'];
        $created = 0;
        foreach ($prefixes as $p) {
            $domainId = ! empty($domainIds) ? $domainIds[array_rand($domainIds)] : null;
            $domain = $domainId ? Domain::find($domainId) : null;
            $emailAddr = $p . '@' . ($domain?->name ?? 'unknown.test');

            DomainEmail::create([
                'user_id' => $this->pickUserId(),
                'module_id' => $mod?->id,
                'service_provider_id' => $this->pickProviderId(),
                'domain_id' => $domainId,
                'email' => $emailAddr,
                'password' => $this->demoPassword,
                'storage_mb' => [5120, 10240, 15360, 30720, 102400][array_rand([0, 1, 2, 3, 4])],
                'billing_period_months' => 12,
                'cost' => round(mt_rand(300, 5000) / 100, 2),
                'expiry_date' => $this->randomDate('-1 month', '+1 year'),
                'status' => $this->pickStatus($statuses),
                'description' => 'Demo email: ' . $emailAddr,
            ]);
            $created++;
        }
        // 3 more with unique names
        foreach (['security', 'abuse', 'webmaster'] as $p) {
            $domainId = ! empty($domainIds) ? $domainIds[array_rand($domainIds)] : null;
            $domain = $domainId ? Domain::find($domainId) : null;
            DomainEmail::create([
                'user_id' => $this->pickUserId(),
                'module_id' => $mod?->id,
                'service_provider_id' => $this->pickProviderId(),
                'domain_id' => $domainId,
                'email' => $p . '@' . ($domain?->name ?? 'unknown.test'),
                'password' => $this->demoPassword,
                'storage_mb' => 5120,
                'billing_period_months' => 12,
                'cost' => 0,
                'status' => 'active',
                'description' => 'Demo email: ' . $p,
            ]);
            $created++;
        }
        $this->command?->info("  Domain Emails: $created");
    }

    private function createVoipRecords(): void
    {
        $mod = $this->getMod('voip');
        $types = ['sip', 'trunk', 'phone'];
        $directions = ['inbound', 'outbound', 'both'];
        $statuses = ['active', 'active', 'active', 'expired', 'cancelled'];
        $numberStatuses = ['active', 'active', 'blocked', 'forwarding'];

        $data = [
            ['name' => 'Main Office Line', 'phone' => '+1-212-555-0100', 'ext_count' => 3],
            ['name' => 'Sales Department', 'phone' => '+1-212-555-0200', 'ext_count' => 4],
            ['name' => 'Support Hotline', 'phone' => '+1-212-555-0300', 'ext_count' => 5],
            ['name' => 'Executive Suite', 'phone' => '+1-212-555-0400', 'ext_count' => 2],
            ['name' => 'Conference Bridge', 'phone' => '+1-212-555-0500', 'ext_count' => 1],
            ['name' => 'Call Center 1', 'phone' => '+1-312-555-0100', 'ext_count' => 8],
            ['name' => 'Call Center 2', 'phone' => '+1-312-555-0200', 'ext_count' => 8],
            ['name' => 'Remote Workers', 'phone' => '+1-415-555-0100', 'ext_count' => 6],
            ['name' => 'Fax Line', 'phone' => '+1-212-555-0600', 'ext_count' => 1],
            ['name' => 'Emergency Line', 'phone' => '+1-212-555-0999', 'ext_count' => 1],
            ['name' => 'Development SIP', 'phone' => '+1-512-555-0100', 'ext_count' => 3],
            ['name' => 'QA Test Trunk', 'phone' => '+1-512-555-0200', 'ext_count' => 2],
            ['name' => 'Marketing Line', 'phone' => '+1-310-555-0100', 'ext_count' => 3],
            ['name' => 'Partner Hotline', 'phone' => '+1-702-555-0100', 'ext_count' => 2],
            ['name' => 'Reception Desk', 'phone' => '+1-212-555-0001', 'ext_count' => 1],
        ];

        $created = 0;
        foreach ($data as $d) {
            $extensions = [];
            $baseExt = 100 + $created * 10;
            for ($i = 0; $i < $d['ext_count']; $i++) {
                $extensions[] = (string)($baseExt + $i);
            }

            $startDate = $this->randomDate('-2 years', '-1 month');
            $expiryDate = date('Y-m-d', strtotime($startDate . ' + ' . mt_rand(6, 24) . ' months'));

            Voip::create([
                'user_id' => $this->pickUserId(),
                'module_id' => $mod?->id,
                'service_provider_id' => $this->pickProviderId(),
                'name' => $d['name'],
                'extensions' => $extensions,
                'phone_number' => $d['phone'],
                'type' => $types[array_rand($types)],
                'direction' => $directions[array_rand($directions)],
                'username' => 'sip_' . Str::slug($d['name']),
                'password' => $this->demoPassword,
                'extension_password' => $this->demoPassword,
                'dashboard_url' => 'https://voip-demo.' . Str::slug($d['name']) . '.example.test',
                'server_ip' => '198.51.100.' . mt_rand(10, 200),
                'billing_period_months' => 12,
                'cost' => round(mt_rand(500, 20000) / 100, 2),
                'start_date' => $startDate,
                'expiry_date' => $expiryDate,
                'status' => $this->pickStatus($statuses),
                'number_status' => $numberStatuses[array_rand($numberStatuses)],
                'outbound_code' => '9',
                'team_details' => 'Demo team for ' . $d['name'],
                'description' => 'Demo VoIP: ' . $d['name'],
            ]);
            $created++;
        }
        $this->command?->info("  VoIP: $created");
    }

    private function createOtherServices(): void
    {
        $mod = $this->getMod('other-services');
        $types = ['saas', 'api', 'monitoring', 'analytics', 'cdn', 'ssl', 'other'];
        $statuses = ['active', 'active', 'active', 'active', 'expired', 'cancelled'];

        $data = [
            ['name' => 'Slack Pro', 'type' => 'saas', 'url' => 'https://slack.com'],
            ['name' => 'GitHub Enterprise', 'type' => 'saas', 'url' => 'https://github.com'],
            ['name' => 'GitLab Premium', 'type' => 'saas', 'url' => 'https://gitlab.com'],
            ['name' => 'Datadog Pro', 'type' => 'monitoring', 'url' => 'https://datadoghq.com'],
            ['name' => 'New Relic', 'type' => 'monitoring', 'url' => 'https://newrelic.com'],
            ['name' => 'Sentry Pro', 'type' => 'monitoring', 'url' => 'https://sentry.io'],
            ['name' => 'Cloudflare Pro', 'type' => 'cdn', 'url' => 'https://cloudflare.com'],
            ['name' => 'Fastly CDN', 'type' => 'cdn', 'url' => 'https://fastly.com'],
            ['name' => 'Stripe Payments', 'type' => 'api', 'url' => 'https://stripe.com'],
            ['name' => 'Paddle Billing', 'type' => 'api', 'url' => 'https://paddle.com'],
            ['name' => 'Mailchimp', 'type' => 'saas', 'url' => 'https://mailchimp.com'],
            ['name' => 'Intercom', 'type' => 'saas', 'url' => 'https://intercom.com'],
            ['name' => 'Jira Cloud', 'type' => 'saas', 'url' => 'https://atlassian.com'],
            ['name' => 'Confluence', 'type' => 'saas', 'url' => 'https://atlassian.com'],
            ['name' => 'PagerDuty', 'type' => 'monitoring', 'url' => 'https://pagerduty.com'],
            ['name' => 'Statuspage', 'type' => 'saas', 'url' => 'https://atlassian.com'],
            ['name' => 'LetsEncrypt', 'type' => 'ssl', 'url' => 'https://letsencrypt.org'],
            ['name' => 'Sectigo SSL', 'type' => 'ssl', 'url' => 'https://sectigo.com'],
            ['name' => 'Google Analytics', 'type' => 'analytics', 'url' => 'https://analytics.google.com'],
            ['name' => 'Mixpanel', 'type' => 'analytics', 'url' => 'https://mixpanel.com'],
            ['name' => 'Hotjar', 'type' => 'analytics', 'url' => 'https://hotjar.com'],
            ['name' => 'AWS CloudWatch', 'type' => 'monitoring', 'url' => 'https://aws.amazon.com/cloudwatch'],
        ];

        $created = 0;
        foreach ($data as $d) {
            $startDate = $this->randomDate('-2 years', '-1 month');
            $expiryDate = date('Y-m-d', strtotime($startDate . ' + ' . mt_rand(1, 24) . ' months'));

            OtherService::create([
                'user_id' => $this->pickUserId(),
                'module_id' => $mod?->id,
                'service_provider_id' => $this->pickProviderId(),
                'name' => $d['name'],
                'service_type' => $d['type'],
                'username' => 'demo_' . Str::slug($d['name']),
                'password' => $this->demoPassword,
                'login_url' => $d['url'] . '/login',
                'website' => $d['url'],
                'billing_period_months' => 12,
                'cost' => round(mt_rand(500, 50000) / 100, 2),
                'start_date' => $startDate,
                'expiry_date' => $expiryDate,
                'status' => $this->pickStatus($statuses),
                'description' => 'Demo ' . $d['name'] . ' account',
                'monitoring_url' => $d['url'] . '/health',
                'last_ping_at' => mt_rand(0, 1) ? now()->subMinutes(mt_rand(1, 1440)) : null,
            ]);
            $created++;
        }
        $this->command?->info("  Other Services: $created");
    }

    private function createExpiryTrackers(): void
    {
        $mod = $this->getMod('expiry-trackers');
        $statuses = ['active', 'active', 'expired', 'pending_renewal', 'cancelled'];
        $smtpProfiles = SmtpProfile::pluck('id')->toArray();

        $data = [
            ['name' => 'SSL Wildcard Certificate', 'days_to_expiry' => -15, 'renewal_offset' => -30],
            ['name' => 'SSL Single Domain', 'days_to_expiry' => -5, 'renewal_offset' => -15],
            ['name' => 'Code Signing Cert', 'days_to_expiry' => -45, 'renewal_offset' => -60],
            ['name' => 'SSL SAN Certificate', 'days_to_expiry' => 3, 'renewal_offset' => -10],
            ['name' => 'EV SSL Certificate', 'days_to_expiry' => 7, 'renewal_offset' => -7],
            ['name' => 'Domain Privacy', 'days_to_expiry' => 10, 'renewal_offset' => -5],
            ['name' => 'WHOIS Guard', 'days_to_expiry' => 14, 'renewal_offset' => -3],
            ['name' => 'SSL Multi-Domain', 'days_to_expiry' => 21, 'renewal_offset' => -14],
            ['name' => 'SSL Organization Validation', 'days_to_expiry' => 28, 'renewal_offset' => -7],
            ['name' => 'SSL Business Validation', 'days_to_expiry' => 45, 'renewal_offset' => -14],
            ['name' => 'Marketing License Renewal', 'days_to_expiry' => 60, 'renewal_offset' => -30],
            ['name' => 'Software Maintenance', 'days_to_expiry' => 90, 'renewal_offset' => -30],
            ['name' => 'Plugin Subscription', 'days_to_expiry' => 180, 'renewal_offset' => -30],
            ['name' => 'Theme License', 'days_to_expiry' => 365, 'renewal_offset' => -30],
            ['name' => 'SSL Extended Validation', 'days_to_expiry' => -30, 'renewal_offset' => -60],
            ['name' => 'Firewall License', 'days_to_expiry' => -60, 'renewal_offset' => -90],
            ['name' => 'Antivirus Subscription', 'days_to_expiry' => 30, 'renewal_offset' => -7],
            ['name' => 'Backup Service', 'days_to_expiry' => 5, 'renewal_offset' => -14],
            ['name' => 'CDN Subscription', 'days_to_expiry' => 15, 'renewal_offset' => -14],
            ['name' => 'Monitoring License', 'days_to_expiry' => -90, 'renewal_offset' => -120],
            ['name' => 'Database License', 'days_to_expiry' => -365, 'renewal_offset' => -395],
            ['name' => 'VPS Backup Addon', 'days_to_expiry' => 60, 'renewal_offset' => -14],
            ['name' => 'Email Marketing Plan', 'days_to_expiry' => 120, 'renewal_offset' => -30],
            ['name' => 'SMS Gateway', 'days_to_expiry' => 45, 'renewal_offset' => -14],
            ['name' => 'API Access Plan', 'days_to_expiry' => -180, 'renewal_offset' => -200],
            ['name' => 'Invoice Service', 'days_to_expiry' => 2, 'renewal_offset' => -14],
            ['name' => 'Hosting Renewal', 'days_to_expiry' => 8, 'renewal_offset' => -7],
            ['name' => 'VPS Renewal', 'days_to_expiry' => 30, 'renewal_offset' => -14],
            ['name' => 'Domain Renewal', 'days_to_expiry' => -1, 'renewal_offset' => -7],
            ['name' => 'SSL Auto-Renew', 'days_to_expiry' => 90, 'renewal_offset' => -30],
            ['name' => 'DDoS Protection', 'days_to_expiry' => 150, 'renewal_offset' => -30],
            ['name' => 'Load Balancer Addon', 'days_to_expiry' => 200, 'renewal_offset' => -30],
        ];

        $created = 0;
        foreach ($data as $d) {
            $expiryDate = date('Y-m-d', strtotime($d['days_to_expiry'] . ' days'));
            $renewalDate = date('Y-m-d', strtotime($d['renewal_offset'] . ' days'));

            $status = 'active';
            if ($d['days_to_expiry'] < 0 && $d['days_to_expiry'] > -60) $status = 'expired';
            elseif ($d['days_to_expiry'] < -100) $status = 'cancelled';
            elseif ($d['days_to_expiry'] <= 30 && $d['days_to_expiry'] > 0) $status = 'pending_renewal';

            ExpiryTracker::create([
                'user_id' => $this->pickUserId(),
                'module_id' => $mod?->id,
                'service_provider_id' => $this->pickProviderId(),
                'name' => $d['name'],
                'username' => 'demo_' . Str::slug($d['name']),
                'login_url' => 'https://manage.example.test/renew/' . Str::slug($d['name']),
                'expiry_date' => $expiryDate,
                'renewal_date' => $renewalDate,
                'billing_period_months' => 12,
                'cost' => round(mt_rand(1000, 99900) / 100, 2),
                'status' => $status,
                'description' => 'Demo expiry tracker: ' . $d['name'],
                'email_notifications_enabled' => (bool) mt_rand(0, 1),
                'smtp_profile_id' => ! empty($smtpProfiles) ? $smtpProfiles[array_rand($smtpProfiles)] : null,
                'notify_days_before' => [30, 15, 7, 1],
                'notify_on_expiry_day' => true,
                'notify_assigned_user' => true,
                'notify_admins' => false,
                'next_notification_due_at' => date('Y-m-d', strtotime($d['days_to_expiry'] . ' days')),
                'monitoring_url' => 'https://monitor.example.test/expiry/' . Str::slug($d['name']),
            ]);
            $created++;
        }
        $this->command?->info("  Expiry Trackers: $created");
    }

    private function createExpiryTrackerNotifications(): void
    {
        $expiryTrackers = ExpiryTracker::inRandomOrder()->take(12)->get();
        $smtpProfiles = SmtpProfile::pluck('id')->toArray();
        $sources = ['cron', 'manual', 'test'];
        $statuses = ['sent', 'sent', 'sent', 'failed'];

        $created = 0;
        foreach ($expiryTrackers as $et) {
            ExpiryTrackerNotification::create([
                'expiry_tracker_id' => $et->id,
                'smtp_profile_id' => ! empty($smtpProfiles) ? $smtpProfiles[array_rand($smtpProfiles)] : null,
                'sender_email' => 'notifications@example.test',
                'reminder_day' => mt_rand(-7, 30),
                'recipient_email' => 'admin@example.test',
                'recipient_type' => 'admin',
                'trigger_source' => $sources[array_rand($sources)],
                'status' => $statuses[array_rand($statuses)],
                'sent_at' => now()->subDays(mt_rand(0, 14)),
                'error_message' => null,
            ]);
            $created++;
        }
        $this->command?->info("  Expiry Tracker Notifications: $created");
    }

    private function createAssets(): void
    {
        $mod = $this->getMod('assets');
        $locId = \App\Models\AssetLocation::first()?->id ?? 1;
        $assetStatuses = ['available', 'assigned', 'assigned', 'lost', 'decommissioned'];
        $conditions = ['new', 'good', 'good', 'fair', 'poor', 'damaged'];
        $departments = ['Engineering', 'Marketing', 'Sales', 'HR', 'Finance', 'IT', 'Operations', 'Legal'];

        // Map exact type IDs from AssetTypeSeeder (IDs are deterministic based on INSERT order in dump)
        // Laptop(cat1): type 1-5, Headphone(cat2): type 6-8, Mouse(cat3): type 9-11, Network(cat4): type 12-19
        $laptopTypeId = fn($brand) => match ($brand) {
            'Dell Latitude 5540' => 1, 'Dell Latitude 7440' => 2,
            'HP EliteBook 840 G10' => 3, 'Lenovo ThinkPad X1 Carbon Gen 11' => 4,
            'Apple MacBook Pro 14"' => 5, default => 1,
        };

        $created = 0;
        $laptops = [
            ['brand' => 'Dell', 'model' => 'Latitude 5540', 'p' => 'Intel Core i5-1345U', 'ram' => '16GB', 'st' => '512GB SSD', 'os' => 'Windows 11 Pro', 'tid' => 1],
            ['brand' => 'Dell', 'model' => 'Latitude 7440', 'p' => 'Intel Core i7-1365U', 'ram' => '32GB', 'st' => '1TB SSD', 'os' => 'Windows 11 Pro', 'tid' => 2],
            ['brand' => 'HP', 'model' => 'EliteBook 840 G10', 'p' => 'Intel Core i5-1345U', 'ram' => '16GB', 'st' => '512GB SSD', 'os' => 'Windows 11 Pro', 'tid' => 3],
            ['brand' => 'Lenovo', 'model' => 'ThinkPad X1 Carbon Gen 11', 'p' => 'Intel Core i7-1365U', 'ram' => '32GB', 'st' => '1TB SSD', 'os' => 'Ubuntu 22.04', 'tid' => 4],
            ['brand' => 'Apple', 'model' => 'MacBook Pro 14" M3', 'p' => 'Apple M3 Pro', 'ram' => '18GB', 'st' => '512GB SSD', 'os' => 'macOS Sonoma', 'tid' => 5],
        ];
        foreach ($laptops as $spec) {
            $created++;
            Asset::create([
                'user_id' => $this->pickUserId(), 'module_id' => $mod?->id,
                'asset_tag' => 'LAP-' . str_pad((string)$created, 5, '0', STR_PAD_LEFT),
                'brand' => $spec['brand'], 'model' => $spec['model'],
                'processor' => $spec['p'], 'ram' => $spec['ram'], 'storage' => $spec['st'], 'os' => $spec['os'],
                'category_id' => 1, 'type_id' => $spec['tid'],
                'serial_number' => 'SN-DEMO-' . strtoupper(Str::random(8)),
                'status' => 'assigned', 'assigned_to' => $this->pickUserId(),
                'assigned_user_name' => User::find($this->pickUserId())?->name,
                'department' => $departments[array_rand($departments)],
                'location_id' => $locId, 'premises' => 'Main Office',
                'issue_date' => $this->randomDate('-2 years', '-1 month'),
                'condition' => $conditions[array_rand($conditions)],
                'description' => 'Demo laptop: ' . $spec['brand'] . ' ' . $spec['model'],
                'anydesk_id' => mt_rand(0, 1) ? strval(mt_rand(100000000, 999999999)) : null,
                'anydesk_password' => mt_rand(0, 1) ? $this->demoPassword : null,
            ]);
        }

        // Desktops (cat 1, type 1)
        foreach (['Dell OptiPlex 7010', 'HP EliteDesk 800 G9', 'Apple Mac Mini M2'] as $model) {
            $created++;
            Asset::create([
                'user_id' => $this->pickUserId(), 'module_id' => $mod?->id,
                'asset_tag' => 'DESK-' . str_pad((string)$created, 5, '0', STR_PAD_LEFT),
                'brand' => explode(' ', $model)[0], 'model' => $model,
                'category_id' => 1, 'type_id' => 1,
                'serial_number' => 'SN-DEMO-' . strtoupper(Str::random(8)),
                'status' => $this->pickStatus($assetStatuses),
                'department' => $departments[array_rand($departments)],
                'location_id' => $locId, 'premises' => 'Main Office',
                'condition' => $conditions[array_rand($conditions)],
                'description' => 'Demo desktop: ' . $model,
            ]);
        }

        // Monitors (cat 1, type 1)
        foreach (['Dell U2723QE 4K', 'LG 27UK850-W 4K', 'Apple Studio Display', 'Samsung Odyssey G7'] as $model) {
            $created++;
            Asset::create([
                'user_id' => $this->pickUserId(), 'module_id' => $mod?->id,
                'asset_tag' => 'MON-' . str_pad((string)$created, 5, '0', STR_PAD_LEFT),
                'brand' => explode(' ', $model)[0], 'model' => $model,
                'category_id' => 1, 'type_id' => 1,
                'serial_number' => 'SN-DEMO-' . strtoupper(Str::random(8)),
                'status' => 'available',
                'department' => $departments[array_rand($departments)],
                'location_id' => $locId, 'premises' => 'Warehouse',
                'condition' => 'new',
                'description' => 'Demo monitor: ' . $model,
            ]);
        }

        // Network equipment (cat 4, types 12-16)
        $netBrands = [
            ['brand' => 'Cisco', 'model' => 'Catalyst 9300-24T', 'tid' => 12],
            ['brand' => 'Ubiquiti', 'model' => 'EdgeRouter 12', 'tid' => 14],
            ['brand' => 'Fortinet', 'model' => 'FortiGate 60F', 'tid' => 15],
            ['brand' => 'MikroTik', 'model' => 'RB4011iGS+RM', 'tid' => 13],
            ['brand' => 'Cisco', 'model' => 'IP Phone 8845', 'tid' => 17],
        ];
        foreach ($netBrands as $spec) {
            $created++;
            Asset::create([
                'user_id' => $this->pickUserId(), 'module_id' => $mod?->id,
                'asset_tag' => 'NET-' . str_pad((string)$created, 5, '0', STR_PAD_LEFT),
                'brand' => $spec['brand'], 'model' => $spec['model'],
                'category_id' => 4, 'type_id' => $spec['tid'],
                'serial_number' => 'SN-DEMO-' . strtoupper(Str::random(8)),
                'status' => $this->pickStatus($assetStatuses),
                'department' => 'IT', 'location_id' => $locId, 'premises' => 'Server Room',
                'condition' => 'good',
                'description' => 'Demo network: ' . $spec['brand'] . ' ' . $spec['model'],
            ]);
        }

        // Peripherals — Headphones (cat 2, type 6)
        foreach (['Sony WH-1000XM5', 'Jabra Evolve2 65', 'Logitech Zone 900'] as $model) {
            $created++;
            Asset::create([
                'user_id' => $this->pickUserId(), 'module_id' => $mod?->id,
                'asset_tag' => 'HPH-' . str_pad((string)$created, 5, '0', STR_PAD_LEFT),
                'brand' => explode(' ', $model)[0], 'model' => $model,
                'category_id' => 2, 'type_id' => 6,
                'serial_number' => 'SN-DEMO-' . strtoupper(Str::random(8)),
                'status' => $this->pickStatus($assetStatuses),
                'assigned_to' => mt_rand(0, 1) ? $this->pickUserId() : null,
                'department' => $departments[array_rand($departments)],
                'location_id' => $locId,
                'condition' => $conditions[array_rand($conditions)],
                'description' => 'Demo headphone: ' . $model,
            ]);
        }

        // Peripherals — Mice (cat 3, type 9)
        foreach (['Logitech MX Master 3S', 'Microsoft Surface Mouse', 'Razer DeathAdder V3'] as $model) {
            $created++;
            Asset::create([
                'user_id' => $this->pickUserId(), 'module_id' => $mod?->id,
                'asset_tag' => 'MOU-' . str_pad((string)$created, 5, '0', STR_PAD_LEFT),
                'brand' => explode(' ', $model)[0], 'model' => $model,
                'category_id' => 3, 'type_id' => 9,
                'serial_number' => 'SN-DEMO-' . strtoupper(Str::random(8)),
                'status' => $this->pickStatus($assetStatuses),
                'assigned_to' => mt_rand(0, 1) ? $this->pickUserId() : null,
                'department' => $departments[array_rand($departments)],
                'location_id' => $locId,
                'condition' => $conditions[array_rand($conditions)],
                'description' => 'Demo mouse: ' . $model,
            ]);
        }

        $this->command?->info("  Assets: $created");
    }

    private function createGMails(): void
    {
        $mod = $this->getMod('g-mails');
        $statuses = ['active', 'active', 'active', 'inactive', 'suspended'];

        $data = [
            ['user_name' => 'Alice Johnson', 'email' => 'alice.johnson', 'pseudo' => 'alice.j', 'dept' => 'Engineering'],
            ['user_name' => 'Bob Smith', 'email' => 'bob.smith', 'pseudo' => 'bob.s', 'dept' => 'Sales'],
            ['user_name' => 'Charlie Brown', 'email' => 'charlie.brown', 'pseudo' => 'charlie.b', 'dept' => 'Marketing'],
            ['user_name' => 'Diana Prince', 'email' => 'diana.prince', 'pseudo' => 'diana.p', 'dept' => 'HR'],
            ['user_name' => 'Eve Martinez', 'email' => 'eve.martinez', 'pseudo' => 'eve.m', 'dept' => 'Finance'],
            ['user_name' => 'Frank Castle', 'email' => 'frank.castle', 'pseudo' => 'frank.c', 'dept' => 'IT'],
            ['user_name' => 'IT Support', 'email' => 'support.team', 'pseudo' => 'support', 'dept' => 'IT'],
            ['user_name' => 'Sales Team', 'email' => 'sales.team', 'pseudo' => 'sales', 'dept' => 'Sales'],
            ['user_name' => 'HR Department', 'email' => 'hr.team', 'pseudo' => 'hr', 'dept' => 'HR'],
            ['user_name' => 'Admin Team', 'email' => 'admin.team', 'pseudo' => 'admin', 'dept' => 'Administration'],
            ['user_name' => 'Marketing Team', 'email' => 'marketing.team', 'pseudo' => 'marketing', 'dept' => 'Marketing'],
            ['user_name' => 'Dev Team', 'email' => 'dev.team', 'pseudo' => 'developers', 'dept' => 'Engineering'],
            ['user_name' => 'Finance Team', 'email' => 'finance.team', 'pseudo' => 'finance', 'dept' => 'Finance'],
            ['user_name' => 'Operations', 'email' => 'ops.team', 'pseudo' => 'operations', 'dept' => 'Operations'],
            ['user_name' => 'Security Team', 'email' => 'security.team', 'pseudo' => 'security', 'dept' => 'IT'],
            ['user_name' => 'QA Team', 'email' => 'qa.team', 'pseudo' => 'quality', 'dept' => 'Engineering'],
        ];

        $created = 0;
        foreach ($data as $d) {
            GMail::create([
                'user_id' => $this->pickUserId(),
                'module_id' => $mod?->id,
                'status' => $this->pickStatus($statuses),
                'user_name' => $d['user_name'],
                'pseudo' => $d['pseudo'],
                'emails_address' => $d['email'] . '@example.test',
                'password' => $this->demoPassword,
                'security_number' => 'SEC-' . strtoupper(Str::random(8)),
                'security_number_person' => User::find($this->pickUserId())?->name ?? 'Unknown',
                'recovery_email' => 'recovery.' . $d['email'] . '@example.test',
                'department' => $d['dept'],
                'assigned' => User::find($this->pickUserId())?->name ?? 'Unknown',
                'user_remarks' => 'Demo G-Mail account for ' . $d['user_name'],
                'comments' => 'Created for local development testing purposes only.',
            ]);
            $created++;
        }
        $this->command?->info("  G-Mails: $created");
    }

    private function createVaultEntries(): void
    {
        $vaultMod = $this->getMod('vault');
        $assetMod = $this->getMod('assets');

        // Owner-scoped (My Credentials) — 12 records spread across users
        $myData = [
            ['service' => 'AWS IAM Admin', 'url' => 'https://console.aws.amazon.com', 'username' => 'admin@demo.test'],
            ['service' => 'GitHub Personal Token', 'url' => 'https://github.com/settings/tokens', 'username' => 'demo-user'],
            ['service' => 'Stripe API Key (Test)', 'url' => 'https://dashboard.stripe.com/apikeys', 'username' => 'sk_test_demo'],
            ['service' => 'Slack API Token', 'url' => 'https://api.slack.com/apps', 'username' => 'xoxb-demo-token'],
            ['service' => 'MySQL Root Password', 'url' => null, 'username' => 'root'],
            ['service' => 'Redis Auth Token', 'url' => null, 'username' => 'default'],
            ['service' => 'Mailgun API Key', 'url' => 'https://mailgun.com', 'username' => 'api:key-demo'],
            ['service' => 'Twilio Account SID', 'url' => 'https://console.twilio.com', 'username' => 'AC_demo_sid'],
            ['service' => 'Cloudflare API Token', 'url' => 'https://dash.cloudflare.com/profile/api-tokens', 'username' => 'demo-cf-token'],
            ['service' => 'DigitalOcean PAT', 'url' => 'https://cloud.digitalocean.com/account/api/tokens', 'username' => 'do_demo_token'],
            ['service' => 'Docker Hub Password', 'url' => 'https://hub.docker.com', 'username' => 'demo-push-user'],
            ['service' => 'Composer Auth Token', 'url' => 'https://packagist.org', 'username' => 'demo-composer'],
        ];

        $created = 0;
        foreach ($myData as $d) {
            $entry = new VaultEntry;
            $entry->user_id = $this->pickUserId();
            $entry->module_id = $vaultMod?->id;
            $entry->service_name = $d['service'];
            $entry->service_url = $d['url'];
            $entry->username = $d['username'];
            $entry->encryptPassword($this->demoPassword);
            $entry->description = 'Demo vault entry: ' . $d['service'];
            $entry->save();
            $created++;
        }

        // Shared credentials (no specific module category)
        $sharedData = [
            ['service' => 'Demo WiFi Password', 'url' => null, 'username' => 'demo-guest'],
            ['service' => 'VPN PSK', 'url' => null, 'username' => 'vpn-demo'],
            ['service' => 'Admin Panel 2FA Backup', 'url' => 'https://admin.example.test/2fa', 'username' => 'admin@demo'],
            ['service' => 'SSH Deploy Key (Production)', 'url' => null, 'username' => 'deploy-bot'],
            ['service' => 'SSL Private Key', 'url' => null, 'username' => 'wildcard-demo'],
            ['service' => 'Database Read-Only User', 'url' => null, 'username' => 'reader_demo'],
            ['service' => 'Demo LDAP Bind Password', 'url' => null, 'username' => 'cn=admin,dc=demo'],
            ['service' => 'SMTP API Key', 'url' => null, 'username' => 'sg_demo_api_key'],
            ['service' => 'Demo OAuth Client Secret', 'url' => null, 'username' => 'demo-client-id'],
            ['service' => 'Test Payment Gateway Key', 'url' => 'https://sandbox.example.test', 'username' => 'merchant_demo'],
            ['service' => 'Demo S3 Access Key', 'url' => 'https://s3.console.example.test', 'username' => 'AKIADEMO12345'],
            ['service' => 'Demo S3 Secret Key', 'url' => null, 'username' => 'demo-s3-user'],
            ['service' => 'Demo SSH Key Passphrase', 'url' => null, 'username' => 'id_rsa_demo'],
            ['service' => 'Demo Encryption Key', 'url' => null, 'username' => 'aes-256-demo'],
            ['service' => 'Demo Webhook HMAC Secret', 'url' => null, 'username' => 'whsec_demo_secret'],
        ];

        foreach ($sharedData as $d) {
            $entry = new VaultEntry;
            $entry->user_id = $this->userIds[0];
            $entry->module_id = null;
            $entry->service_name = $d['service'];
            $entry->service_url = $d['url'];
            $entry->username = $d['username'];
            $entry->encryptPassword($this->demoPassword);
            $entry->description = 'Demo shared vault entry: ' . $d['service'];
            $entry->save();
            $created++;
        }

        $this->command?->info("  Vault Entries: $created");
    }

    private function createTasks(): void
    {
        $mods = Module::whereIn('slug', ['hostings', 'vps', 'domains', 'voip', 'service-providers', 'domain-emails', 'other-services', 'expiry-trackers'])->pluck('id', 'slug');
        $statuses = ['pending', 'pending', 'in_progress', 'in_progress', 'completed', 'completed', 'cancelled'];
        $priorities = ['low', 'medium', 'medium', 'high', 'high', 'urgent'];

        $taskData = [
            ['title' => 'Update SSL certificate on main website', 'mod' => 'hostings', 'due' => '-5'],
            ['title' => 'Migrate DNS to Cloudflare', 'mod' => 'domains', 'due' => '+3'],
            ['title' => 'Apply security patches to production servers', 'mod' => 'vps', 'due' => '-1'],
            ['title' => 'Renew domain: example.test', 'mod' => 'domains', 'due' => '+7'],
            ['title' => 'Configure monitoring alerts for VPS CPU', 'mod' => 'vps', 'due' => '+14'],
            ['title' => 'Test VoIP failover to backup trunk', 'mod' => 'voip', 'due' => '+21'],
            ['title' => 'Update service provider billing contacts', 'mod' => 'service-providers', 'due' => '+10'],
            ['title' => 'Audit domain email accounts', 'mod' => 'domain-emails', 'due' => '+5'],
            ['title' => 'Review SaaS subscription costs', 'mod' => 'other-services', 'due' => '+30'],
            ['title' => 'Fix expired SSL certificate on staging', 'mod' => 'hostings', 'due' => '-3'],
            ['title' => 'Upgrade VPS plan for database server', 'mod' => 'vps', 'due' => '+60'],
            ['title' => 'Add SPF/DKIM records for all domains', 'mod' => 'domains', 'due' => '+15'],
            ['title' => 'Update VoIP extension mappings', 'mod' => 'voip', 'due' => '+45'],
            ['title' => 'Renew hosting plan for blog platform', 'mod' => 'hostings', 'due' => '-10'],
            ['title' => 'Migrate VPS from US-East to EU-West', 'mod' => 'vps', 'due' => '+90'],
            ['title' => 'Set up automatic SSL renewal', 'mod' => 'expiry-trackers', 'due' => '+7'],
            ['title' => 'Clean up unused domain records', 'mod' => 'domains', 'due' => '+14'],
            ['title' => 'Configure backup schedules for VPS', 'mod' => 'vps', 'due' => '+2'],
            ['title' => 'Test email delivery from domain emails', 'mod' => 'domain-emails', 'due' => '+1'],
            ['title' => 'Update firewall rules on load balancer', 'mod' => 'vps', 'due' => '+4'],
            ['title' => 'Review and rotate API keys', 'mod' => 'other-services', 'due' => '+30'],
            ['title' => 'Audit user permissions on all modules', 'mod' => null, 'due' => '+60'],
            ['title' => 'Update disaster recovery plan', 'mod' => null, 'due' => '+7'],
            ['title' => 'Test backup restoration process', 'mod' => 'vps', 'due' => '+14'],
            ['title' => 'Set up cron jobs for monitoring checks', 'mod' => null, 'due' => '-2'],
            ['title' => 'Configure SMTP profiles for notifications', 'mod' => null, 'due' => '+10'],
            ['title' => 'Migrate old hosting accounts to new provider', 'mod' => 'hostings', 'due' => '+30'],
            ['title' => 'Verify WHOIS contacts for all domains', 'mod' => 'domains', 'due' => '+21'],
            ['title' => 'Create asset inventory report', 'mod' => null, 'due' => '+5'],
            ['title' => 'Review ExpiryTracker notification settings', 'mod' => 'expiry-trackers', 'due' => '+3'],
            ['title' => 'Update team contact information', 'mod' => null, 'due' => '+90'],
            ['title' => 'Test credential reveal flow for vault', 'mod' => null, 'due' => '+1'],
            ['title' => 'Generate monthly cost report', 'mod' => null, 'due' => '-7'],
        ];

        $created = 0;
        foreach ($taskData as $td) {
            $moduleId = $td['mod'] ? ($mods[$td['mod']] ?? null) : null;
            $creatorId = $this->pickUserId();
            $due = date('Y-m-d H:i:s', strtotime($td['due'] . ' days 10:00:00'));

            $task = Task::create([
                'title' => $td['title'],
                'description' => 'Demo task: ' . $td['title'] . '. Created for local development testing.',
                'module_id' => $moduleId,
                'status' => $this->pickStatus($statuses),
                'priority' => $priorities[array_rand($priorities)],
                'due_date' => $due,
                'created_by' => $creatorId,
                'updated_by' => $creatorId,
            ]);

            // Assign 1-3 users
            $assignees = [$creatorId];
            for ($i = 0; $i < mt_rand(0, 2); $i++) {
                $assignees[] = $this->pickUserIdExcept($creatorId);
            }
            $task->assignees()->syncWithoutDetaching(array_unique($assignees));
            $created++;
        }
        $this->command?->info("  Tasks: $created");
    }

    private function createNotes(): void
    {
        $moduleId = $this->getMod('notes')?->id;

        $noteData = [
            ['content' => 'IMPORTANT: All demo passwords use "QA-DEMO-ONLY" for testing.', 'pinned' => true],
            ['content' => 'Server maintenance window: Saturday 2-4 AM UTC.', 'pinned' => true],
            ['content' => 'Remember to rotate API keys every 90 days.', 'pinned' => false],
            ['content' => 'Production database backups stored on S3 with 30-day retention.', 'pinned' => false],
            ['content' => 'Emergency contact: admin@example.test / +1-555-0100', 'pinned' => true],
            ['content' => 'DNS changes may take up to 48 hours to propagate globally.', 'pinned' => false],
            ['content' => 'All SSL certificates managed via automated process in ExpiryTrackers.', 'pinned' => false],
            ['content' => 'New employee onboarding checklist available in HR portal.', 'pinned' => false],
            ['content' => 'VPN credentials for remote access are in Shared Vault.', 'pinned' => false],
            ['content' => 'Deployments follow blue-green strategy for zero downtime.', 'pinned' => false],
            ['content' => 'Log retention policy: 90 days active, 1 year archived.', 'pinned' => false],
            ['content' => 'Monitor all VPS with Datadog agents for metric collection.', 'pinned' => false],
            ['content' => 'Phone system maintenance scheduled for first Sunday of each month.', 'pinned' => false],
            ['content' => 'Review and update incident response runbook quarterly.', 'pinned' => false],
            ['content' => 'Domain expiry dates synced automatically from registrar APIs.', 'pinned' => false],
            ['content' => 'Demo environment credentials should never be used in production.', 'pinned' => true],
        ];

        $created = 0;
        foreach ($noteData as $nd) {
            Note::create([
                'user_id' => $this->pickUserId(),
                'notable_type' => null,
                'notable_id' => null,
                'content' => $nd['content'],
                'is_pinned' => $nd['pinned'],
            ]);
            $created++;
        }
        $this->command?->info("  Notes: $created");
    }

    private function createWebhooks(): void
    {
        $events = [
            ['name' => 'Expiry Reminder Webhook', 'url' => 'https://hooks.example.test/expiry', 'evt' => ['expiring_soon', 'expired']],
            ['name' => 'Monitoring Alert Webhook', 'url' => 'https://hooks.example.test/monitor', 'evt' => ['monitor_down', 'monitor_warning']],
            ['name' => 'Task Update Webhook', 'url' => 'https://hooks.example.test/tasks', 'evt' => ['task_created', 'task_completed']],
            ['name' => 'User Action Webhook', 'url' => 'https://hooks.example.test/users', 'evt' => ['user_created', 'user_suspended']],
            ['name' => 'System Health Webhook', 'url' => 'https://hooks.example.test/health', 'evt' => ['health_critical', 'health_warning']],
            ['name' => 'Import Complete Webhook', 'url' => 'https://hooks.example.test/import', 'evt' => ['import_complete']],
        ];

        $created = 0;
        foreach ($events as $w) {
            Webhook::create([
                'user_id' => $this->userIds[0],
                'name' => $w['name'],
                'url' => $w['url'],
                'events' => $w['evt'],
                'is_active' => (bool) mt_rand(0, 1),
                'last_fired_at' => mt_rand(0, 1) ? now()->subHours(mt_rand(1, 72)) : null,
            ]);
            $created++;
        }
        $this->command?->info("  Webhooks: $created");
    }

    private function createLoginAudits(): void
    {
        $events = ['login_success', 'login_failed', 'logout'];

        $created = 0;
        // Create 5 login audit records for each user
        foreach ($this->userIds as $uid) {
            $user = User::find($uid);
            if (! $user) continue;
            for ($i = 0; $i < 5; $i++) {
                LoginAudit::create([
                    'user_id' => $uid,
                    'email' => $user->email,
                    'ip_address' => '192.0.2.' . mt_rand(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/' . mt_rand(100, 120) . '.0.0.0 Safari/537.36',
                    'event' => $events[array_rand($events)],
                    'created_at' => now()->subDays(mt_rand(0, 30))->subHours(mt_rand(0, 12)),
                ]);
                $created++;
            }
        }
        $this->command?->info("  Login Audits: $created");
    }

    private function createUserModulePermissions(): void
    {
        $modules = Module::where('is_active', true)->get();
        $targetUsers = [$this->userIds[2] ?? null, $this->userIds[4] ?? null, $this->userIds[5] ?? null];

        foreach ($targetUsers as $uid) {
            if (! $uid) continue;
            foreach ($modules as $module) {
                // Skip if already has a permission record
                if (UserModulePermission::where('user_id', $uid)->where('module_id', $module->id)->exists()) {
                    continue;
                }

                $perm = [
                    'user_id' => $uid,
                    'module_id' => $module->id,
                ];

                // Create varied overrides
                switch (mt_rand(0, 4)) {
                    case 0: // Full access override
                        $perm['can_read'] = true; $perm['can_create'] = true;
                        $perm['can_update'] = true; $perm['can_delete'] = true;
                        $perm['can_export'] = true; $perm['can_reveal'] = true;
                        break;
                    case 1: // Read-only override
                        $perm['can_read'] = true;
                        $perm['can_create'] = null; $perm['can_update'] = null;
                        $perm['can_delete'] = null; $perm['can_export'] = null;
                        $perm['can_reveal'] = null;
                        break;
                    case 2: // Deny all override
                        $perm['can_read'] = false; $perm['can_create'] = false;
                        $perm['can_update'] = false; $perm['can_delete'] = false;
                        $perm['can_export'] = false; $perm['can_reveal'] = false;
                        break;
                    case 3: // Create + update only
                        $perm['can_read'] = true; $perm['can_create'] = true;
                        $perm['can_update'] = true; $perm['can_delete'] = false;
                        $perm['can_export'] = null; $perm['can_reveal'] = null;
                        break;
                    case 4: // Reveal-only
                        $perm['can_read'] = true; $perm['can_reveal'] = true;
                        break;
                }
                UserModulePermission::create($perm);
            }
        }
        $this->command?->info('  User Module Permissions created for ' . count($targetUsers) . ' users');
    }

    private function createApiTokens(): void
    {
        foreach ($this->userIds as $uid) {
            $user = User::find($uid);
            if (! $user) continue;
            $user->tokens()->where('name', 'Demo API Token')->delete();
            $user->createToken('Demo API Token', ['*']);

            // Also create an expired token
            $user->tokens()->create([
                'name' => 'Expired Demo Token',
                'token' => hash('sha256', 'expired-demo-token-' . $uid),
                'abilities' => ['read'],
                'expires_at' => now()->subDays(30),
                'last_used_at' => now()->subDays(60),
            ]);
        }
        $this->command?->info('  API Tokens created for ' . count($this->userIds) . ' users');
    }
}
