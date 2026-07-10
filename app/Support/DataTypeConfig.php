<?php

namespace App\Support;

use App\Models\Attachment;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Feature;
use App\Models\GMail;
use App\Models\Hosting;
use App\Models\LoginAudit;
use App\Models\Module;
use App\Models\Note;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Task;
use App\Models\User;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use App\Models\Webhook;
use App\Models\Privilege;
use App\Models\Role;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Activitylog\Models\Activity;

class DataTypeConfig
{
    /** @return array<string, array{model: class-string, columns: string[], admin?: bool, module_slug?: string}> */
    public static function exportTypes(): array
    {
        return [
            'domains' => [
                'model' => Domain::class, 'columns' => ['name', 'service_provider_id', 'registration_date', 'expiry_date', 'auto_renew', 'cost', 'status', 'cloudflare_status', 'dns_servers', 'description', 'created_at'], 'admin' => false, 'module_slug' => 'domains',
            ],
            'hostings' => [
                'model' => Hosting::class, 'columns' => ['name', 'service_provider_id', 'plan', 'domain', 'domain_ip', 'mail_domain_ip', 'cpanel_ip', 'start_date', 'expiry_date', 'cost', 'status', 'description', 'created_at'], 'admin' => false, 'module_slug' => 'hostings',
            ],
            'vps' => [
                'model' => Vps::class, 'columns' => ['name', 'service_provider_id', 'plan', 'ip_address', 'os', 'ram_mb', 'disk_gb', 'cpu_cores', 'department', 'location', 'login_ids', 'additional_ips', 'cost', 'start_date', 'expiry_date', 'status', 'description', 'created_at'], 'admin' => false, 'module_slug' => 'vps',
            ],
            'voip' => [
                'model' => Voip::class, 'columns' => ['name', 'service_provider_id', 'phone_number', 'type', 'direction', 'username', 'server_ip', 'cost', 'expiry_date', 'status', 'number_status', 'outbound_code', 'team_details', 'description', 'created_at'], 'admin' => false, 'module_slug' => 'voip',
            ],
            'service-providers' => [
                'model' => ServiceProvider::class, 'columns' => ['name', 'type', 'website', 'email', 'cost', 'status', 'description', 'created_at'], 'admin' => false, 'module_slug' => 'service-providers',
            ],
            'domain-emails' => [
                'model' => DomainEmail::class, 'columns' => ['email', 'service_provider_id', 'domain_id', 'storage_mb', 'cost', 'expiry_date', 'status', 'description', 'created_at'], 'admin' => false, 'module_slug' => 'domain-emails',
            ],
            'other-services' => [
                'model' => OtherService::class, 'columns' => ['name', 'service_type', 'website', 'cost', 'expiry_date', 'status', 'description', 'created_at'], 'admin' => false, 'module_slug' => 'other-services',
            ],
            'expiry-trackers' => [
                'model' => ExpiryTracker::class, 'columns' => ['name', 'expiry_date', 'cost', 'status', 'description', 'created_at'], 'admin' => false, 'module_slug' => 'expiry-trackers',
            ],
            'assets' => [
                'model' => \App\Models\Asset::class, 'columns' => ['asset_tag', 'category_id', 'type_id', 'serial_number', 'status', 'assigned_to', 'department', 'location_id', 'issue_date', 'return_date', 'condition', 'description', 'created_at'], 'admin' => false, 'module_slug' => 'assets',
            ],
            'g-mails' => [
                'model' => GMail::class, 'columns' => ['status', 'user_name', 'pseudo', 'emails_address', 'security_number', 'security_number_person', 'recovery_email', 'department', 'assigned', 'user_remarks', 'comments', 'created_at'], 'admin' => false, 'module_slug' => 'g-mails',
            ],
            'tasks' => [
                'model' => Task::class, 'columns' => ['title', 'description', 'status', 'priority', 'due_date', 'created_at'], 'admin' => false, 'module_slug' => 'tasks',
            ],
            'vault' => [
                'model' => VaultEntry::class, 'columns' => ['service_name', 'service_url', 'username', 'description', 'created_at'], 'admin' => false, 'module_slug' => 'vault',
            ],
            'notes' => [
                'model' => Note::class, 'columns' => ['content', 'notable_type', 'notable_id', 'created_at'], 'admin' => false, 'module_slug' => 'notes',
            ],
            'features' => [
                'model' => Feature::class, 'columns' => ['name', 'slug', 'description', 'icon', 'is_active', 'created_at'], 'admin' => true,
            ],
            'modules' => [
                'model' => Module::class, 'columns' => ['name', 'feature_id', 'created_at'], 'admin' => true,
            ],
            'webhooks' => [
                'model' => Webhook::class, 'columns' => ['name', 'url', 'events', 'is_active', 'last_fired_at', 'created_at'], 'admin' => true,
            ],
            'activity-logs' => [
                'model' => Activity::class, 'columns' => ['log_name', 'description', 'subject_type', 'subject_id', 'causer_type', 'causer_id', 'event', 'created_at'], 'admin' => true,
            ],
            'login-audits' => [
                'model' => LoginAudit::class, 'columns' => ['user_id', 'email', 'ip_address', 'user_agent', 'event', 'created_at'], 'admin' => true,
            ],
            'attachments' => [
                'model' => Attachment::class, 'columns' => ['filename', 'original_name', 'mime_type', 'size', 'notable_type', 'notable_id', 'created_at'], 'admin' => true,
            ],
            'users' => [
                'model' => User::class, 'columns' => ['name', 'email'], 'admin' => true,
            ],
            'roles' => [
                'model' => Role::class, 'columns' => ['id', 'name', 'slug', 'created_at'], 'admin' => true,
            ],
            'privileges' => [
                'model' => Privilege::class, 'columns' => ['id', 'name', 'slug', 'description', 'created_at'], 'admin' => true,
            ],
            'tokens' => [
                'model' => PersonalAccessToken::class, 'columns' => ['id', 'name', 'created_at', 'last_used_at'], 'admin' => true,
            ],
        ];
    }

    /** @return array<string, class-string> */
    public static function importTypes(): array
    {
        return [
            'domains' => Domain::class,
            'hostings' => Hosting::class,
            'vps' => Vps::class,
            'voip' => Voip::class,
            'service-providers' => ServiceProvider::class,
            'domain-emails' => DomainEmail::class,
            'other-services' => OtherService::class,
            'expiry-trackers' => ExpiryTracker::class,
            'assets' => \App\Models\Asset::class,
            'g-mails' => GMail::class,
            'tasks' => Task::class,
            'vault' => VaultEntry::class,
            'notes' => Note::class,
            'features' => Feature::class,
            'modules' => Module::class,
            'webhooks' => Webhook::class,
            'users' => User::class,
            'roles' => Role::class,
            'privileges' => Privilege::class,
            'activity-logs' => Activity::class,
            'login-audits' => LoginAudit::class,
            'attachments' => Attachment::class,
            'tokens' => PersonalAccessToken::class,
        ];
    }

    /** @return array<string, string> Map of import type => module slug for permission checking */
    public static function importTypeModuleMapping(): array
    {
        return [
            'domains' => 'domains',
            'hostings' => 'hostings',
            'vps' => 'vps',
            'voip' => 'voip',
            'service-providers' => 'service-providers',
            'domain-emails' => 'domain-emails',
            'other-services' => 'other-services',
            'expiry-trackers' => 'expiry-trackers',
            'assets' => 'assets',
            'g-mails' => 'g-mails',
            'tasks' => 'tasks',
            'vault' => 'vault',
            'notes' => 'notes',
            'webhooks' => 'webhooks',
            'users' => 'users',
            'roles' => 'roles',
            'privileges' => 'privileges',
            'activity-logs' => 'activity-logs',
            'login-audits' => 'login-audits',
            'attachments' => 'attachments',
        ];
    }
}
