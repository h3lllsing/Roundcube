<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\Note;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Task;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use App\Models\User;
use App\Models\Module;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $demoPassword = env('DEMO_ENTITY_PASSWORD', Str::random(16));

        $admin = User::where('email', 'admin@tyro.project')->first() ?? User::factory()->create(['email' => 'admin@tyro.project']);
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        if ($superAdminRole && ! $admin->roles->contains($superAdminRole->id)) {
            $admin->roles()->attach($superAdminRole);
        }

        $testUser = User::where('email', 'test@example.com')->first() ?? User::factory()->create(['email' => 'test@example.com']);

        $hostingMod = Module::where('slug', 'hostings')->first();
        $vpsMod = Module::where('slug', 'vps')->first();
        $domainMod = Module::where('slug', 'domains')->first();
        $voipMod = Module::where('slug', 'voip')->first();
        $svcMod = Module::where('slug', 'service-providers')->first();
        $deMod = Module::where('slug', 'domain-emails')->first();
        $osMod = Module::where('slug', 'other-services')->first();
        $etMod = Module::where('slug', 'expiry-trackers')->first();
        $taskMod = Module::where('slug', 'tasks')->first();
        $vaultMod = Module::where('slug', 'vault')->first();

        // Service Providers (deterministic names so firstOrCreate works)
        $serviceProviderEntries = [
            [
                'name' => 'DigitalOcean',
                'type' => 'vps',
                'provider' => 'DigitalOcean Inc.',
                'website' => 'https://digitalocean.com',
                'user_id' => $admin->id,
            ],
            [
                'name' => 'Namecheap',
                'type' => 'domain',
                'provider' => 'Namecheap Inc.',
                'website' => 'https://namecheap.com',
                'user_id' => $admin->id,
            ],
            [
                'name' => 'Google Workspace',
                'type' => 'email',
                'provider' => 'Google LLC',
                'website' => 'https://workspace.google.com',
                'user_id' => $admin->id,
            ],
        ];
        $providerIds = [];
        foreach ($serviceProviderEntries as $sp) {
            $provider = ServiceProvider::firstOrCreate(
                ['name' => $sp['name']],
                [
                    'user_id' => $sp['user_id'],
                    'module_id' => $svcMod?->id,
                    'type' => $sp['type'],
                    'provider' => $sp['provider'],
                    'website' => $sp['website'],
                    'password' => $demoPassword,
                    'cost' => 0,
                    'start_date' => now()->subYear(),
                    'expiry_date' => now()->addYear(),
                    'status' => 'active',
                    'description' => 'Demo service provider.',
                ]
            );
            $providerIds[] = $provider->id;
        }

        // Hosting
        $hostingEntries = [
            [
                'name' => 'Main Website Hosting',
                'plan' => 'Business',
                'domain' => 'example.com',
                'username' => 'mainuser',
                'cost' => 29.99,
                'user_id' => $admin->id,
            ],
            [
                'name' => 'Client Portal Hosting',
                'plan' => 'Premium',
                'domain' => 'clientportal.com',
                'username' => 'portaladmin',
                'cost' => 49.99,
                'user_id' => $testUser->id,
            ],
        ];
        foreach ($hostingEntries as $entry) {
            Hosting::firstOrCreate(
                ['name' => $entry['name']],
                [
                    'user_id' => $entry['user_id'],
                    'module_id' => $hostingMod?->id,
                    'service_provider_id' => $providerIds[array_rand($providerIds)],
                    'username' => $entry['username'],
                    'password' => $demoPassword,
                    'cpanel_url' => 'https://cpanel.' . $entry['domain'],
                    'plan' => $entry['plan'],
                    'domain' => $entry['domain'],
                    'start_date' => now()->subMonths(6),
                    'expiry_date' => now()->addMonths(6),
                    'cost' => $entry['cost'],
                    'status' => 'active',
                    'description' => 'Demo ' . $entry['name'] . ' entry.',
                ]
            );
        }

        // VPS
        $vpsEntries = [
            [
                'name' => 'Production Web Server',
                'plan' => 's-2vcpu-2gb',
                'ip' => '203.0.113.10',
                'os' => 'Ubuntu 24.04',
                'ram' => 2048,
                'disk' => 50,
                'cores' => 2,
                'cost' => 15.00,
                'user_id' => $admin->id,
            ],
            [
                'name' => 'Database Server',
                'plan' => 's-4vcpu-8gb',
                'ip' => '203.0.113.20',
                'os' => 'Debian 12',
                'ram' => 8192,
                'disk' => 160,
                'cores' => 4,
                'cost' => 48.00,
                'user_id' => $testUser->id,
            ],
        ];
        foreach ($vpsEntries as $entry) {
            Vps::firstOrCreate(
                ['name' => $entry['name']],
                [
                    'user_id' => $entry['user_id'],
                    'module_id' => $vpsMod?->id,
                    'service_provider_id' => $providerIds[array_rand($providerIds)],
                    'plan' => $entry['plan'],
                    'ip_address' => $entry['ip'],
                    'password' => $demoPassword,
                    'os' => $entry['os'],
                    'ram_mb' => $entry['ram'],
                    'disk_gb' => $entry['disk'],
                    'cpu_cores' => $entry['cores'],
                    'cost' => $entry['cost'],
                    'start_date' => now()->subMonths(3),
                    'expiry_date' => now()->addMonths(9),
                    'status' => 'active',
                    'description' => 'Demo ' . $entry['name'] . ' entry.',
                ]
            );
        }

        // Domain
        $domainEntries = [
            [
                'name' => 'example.com',
                'provider' => 'Namecheap',
                'cost' => 12.99,
                'user_id' => $admin->id,
            ],
            [
                'name' => 'mysite.org',
                'provider' => 'GoDaddy',
                'cost' => 14.99,
                'user_id' => $testUser->id,
            ],
        ];
        $createdDomainIds = [];
        foreach ($domainEntries as $entry) {
            $domain = Domain::firstOrCreate(
                ['name' => $entry['name']],
                [
                    'user_id' => $entry['user_id'],
                    'module_id' => $domainMod?->id,
                    'service_provider_id' => $providerIds[array_rand($providerIds)],
                    'registration_date' => now()->subYear(),
                    'expiry_date' => now()->addYear(),
                    'auto_renew' => true,
                    'cost' => $entry['cost'],
                    'status' => 'active',
                    'dns_servers' => ['ns1.' . $entry['name'], 'ns2.' . $entry['name']],
                    'description' => 'Demo domain entry.',
                ]
            );
            $createdDomainIds[] = $domain->id;
        }

        // VoIP
        $voipEntries = [
            [
                'name' => 'Main SIP Trunk',
                'extensions' => ['101', '102'],
                'phone_number' => '+1-212-555-0100',
                'type' => 'trunk',
                'direction' => 'both',
                'username' => 'sip_main',
                'server_ip' => '10.0.0.10',
                'cost' => 19.99,
                'user_id' => $admin->id,
            ],
            [
                'name' => 'Sales Phone Line',
                'extensions' => ['201'],
                'phone_number' => '+1-212-555-0200',
                'type' => 'sip',
                'direction' => 'inbound',
                'username' => 'sip_sales',
                'server_ip' => '10.0.0.11',
                'cost' => 14.99,
                'user_id' => $testUser->id,
            ],
        ];
        foreach ($voipEntries as $entry) {
            Voip::firstOrCreate(
                ['name' => $entry['name']],
                [
                    'user_id' => $entry['user_id'],
                    'module_id' => $voipMod?->id,
                    'service_provider_id' => $providerIds[array_rand($providerIds)],
                    'extensions' => $entry['extensions'],
                    'phone_number' => $entry['phone_number'],
                    'type' => $entry['type'],
                    'direction' => $entry['direction'],
                    'username' => $entry['username'],
                    'password' => $demoPassword,
                    'extension_password' => $demoPassword,
                    'dashboard_url' => 'https://voip.' . strtolower(str_replace(' ', '', $entry['name'])) . '.com',
                    'server_ip' => $entry['server_ip'],
                    'cost' => $entry['cost'],
                    'start_date' => now()->subMonths(6),
                    'expiry_date' => now()->addMonths(6),
                    'status' => 'active',
                    'number_status' => 'active',
                    'outbound_code' => '9',
                    'team_details' => 'Demo team: ' . $entry['name'],
                    'description' => 'Demo ' . $entry['name'] . ' entry.',
                ]
            );
        }

        // DomainEmails
        $deEntries = [
            [
                'email' => 'info@example.com',
                'password' => $demoPassword,
                'domain_name' => 'example.com',
                'user_id' => $admin->id,
            ],
            [
                'email' => 'support@example.com',
                'password' => $demoPassword,
                'domain_name' => 'example.com',
                'user_id' => $testUser->id,
            ],
        ];
        foreach ($deEntries as $entry) {
            $linkedDomain = Domain::where('name', $entry['domain_name'])->first();
            DomainEmail::firstOrCreate(
                ['email' => $entry['email']],
                [
                    'user_id' => $entry['user_id'],
                    'module_id' => $deMod?->id,
                    'password' => $entry['password'],
                    'domain_id' => $linkedDomain?->id,
                    'service_provider_id' => $providerIds[array_rand($providerIds)],
                    'storage_mb' => 1024,
                    'cost' => 0.00,
                    'status' => 'active',
                    'description' => 'Demo domain email entry.',
                ]
            );
        }

        // OtherServices
        $osEntries = [
            [
                'name' => 'Slack Premium',
                'type' => 'saas',
                'username' => 'admin',
                'login_url' => 'https://slack.com/signin',
                'cost' => 8.00,
                'user_id' => $admin->id,
            ],
            [
                'name' => 'GitHub Enterprise',
                'type' => 'saas',
                'username' => 'testuser',
                'login_url' => 'https://github.com/login',
                'cost' => 21.00,
                'user_id' => $testUser->id,
            ],
        ];
        foreach ($osEntries as $entry) {
            OtherService::firstOrCreate(
                ['name' => $entry['name']],
                [
                    'user_id' => $entry['user_id'],
                    'module_id' => $osMod?->id,
                    'service_provider_id' => $providerIds[array_rand($providerIds)],
                    'service_type' => $entry['type'],
                    'username' => $entry['username'],
                    'password' => $demoPassword,
                    'login_url' => $entry['login_url'],
                    'cost' => $entry['cost'],
                    'expiry_date' => now()->addYear(),
                    'status' => 'active',
                    'description' => 'Demo ' . $entry['name'] . ' subscription.',
                ]
            );
        }

        // ExpiryTrackers
        $etEntries = [
            [
                'name' => 'SSL Certificate',
                'username' => 'admin',
                'login_url' => 'https://namecheap.com/ssl',
                'cost' => 99.00,
                'user_id' => $admin->id,
            ],
            [
                'name' => 'Code Signing Cert',
                'username' => 'testuser',
                'login_url' => 'https://namecheap.com/code-signing',
                'cost' => 299.00,
                'user_id' => $testUser->id,
            ],
        ];
        foreach ($etEntries as $entry) {
            ExpiryTracker::firstOrCreate(
                ['name' => $entry['name']],
                [
                    'user_id' => $entry['user_id'],
                    'module_id' => $etMod?->id,
                    'service_provider_id' => $providerIds[array_rand($providerIds)],
                    'username' => $entry['username'],
                    'login_url' => $entry['login_url'],
                    'expiry_date' => now()->addMonths(3),
                    'renewal_date' => now()->addMonths(2)->addDays(15),
                    'cost' => $entry['cost'],
                    'status' => 'active',
                    'description' => 'Demo ' . $entry['name'] . ' tracker.',
                ]
            );
        }

        // Notes
        $noteEntries = [
            ['content' => 'Important: Remember to renew the main domain before expiry.', 'user_id' => $admin->id],
            ['content' => 'Server maintenance scheduled for next Saturday at 2 AM.', 'user_id' => $testUser->id],
        ];
        foreach ($noteEntries as $entry) {
            Note::firstOrCreate(
                ['content' => $entry['content']],
                ['user_id' => $entry['user_id']]
            );
        }

        // Tasks
        $taskEntries = [
            [
                'title' => 'Update server OS patches',
                'description' => 'Run apt update and upgrade on all production servers.',
                'status' => 'pending',
                'priority' => 'high',
                'user_id' => $admin->id,
            ],
            [
                'title' => 'Migrate DNS to Cloudflare',
                'description' => 'Transfer DNS management from current provider to Cloudflare.',
                'status' => 'in_progress',
                'priority' => 'medium',
                'user_id' => $testUser->id,
            ],
        ];
        foreach ($taskEntries as $entry) {
            Task::firstOrCreate(
                ['title' => $entry['title']],
                [
                    'description' => $entry['description'],
                    'module_id' => $taskMod?->id,
                    'status' => $entry['status'],
                    'priority' => $entry['priority'],
                    'due_date' => now()->addDays(7),
                    'created_by' => $entry['user_id'],
                    'updated_by' => $entry['user_id'],
                ]
            );
        }

        // Vault
        $vaultEntries = [
            [
                'service_name' => 'AWS Root Account',
                'service_url' => 'https://aws.amazon.com/console',
                'username' => 'admin@example.com',
                'password' => $demoPassword,
                'description' => 'Demo vault entry - AWS root credentials.',
                'user_id' => $admin->id,
            ],
            [
                'service_name' => 'GitHub PAT',
                'service_url' => 'https://github.com/settings/tokens',
                'username' => 'testuser',
                'password' => $demoPassword,
                'description' => 'Demo vault entry - GitHub personal access token.',
                'user_id' => $testUser->id,
            ],
        ];
        foreach ($vaultEntries as $ve) {
            $existing = VaultEntry::where('service_name', $ve['service_name'])
                ->where('username', $ve['username'])
                ->where('user_id', $ve['user_id'])
                ->first();
            if ($existing) {
                continue;
            }
            $entry = new VaultEntry;
            $entry->user_id = $ve['user_id'];
            $entry->module_id = $vaultMod?->id;
            $entry->service_name = $ve['service_name'];
            $entry->service_url = $ve['service_url'];
            $entry->username = $ve['username'];
            $entry->encryptPassword($ve['password']);
            $entry->description = $ve['description'];
            $entry->save();
        }
    }
}
