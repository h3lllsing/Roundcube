<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $softDeleteTables = [
        'assets', 'asset_locations', 'asset_types', 'asset_categories',
        'tasks', 'webhooks', 'expiry_trackers', 'other_services',
        'domain_emails', 'service_providers', 'voip', 'vps',
        'hostings', 'domains', 'password_vault', 'modules',
        'attachments', 'notes', 'users', 'features',
    ];

    private array $statusTables = [
        'domains', 'hostings', 'vps', 'voip', 'domain_emails',
        'other_services', 'service_providers', 'expiry_trackers',
    ];

    public function up(): void
    {
        foreach ($this->softDeleteTables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->index('deleted_at', "{$tbl}_deleted_at_index");
            });
        }

        foreach ($this->statusTables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->index('status', "{$tbl}_status_index");
            });
        }

        Schema::table('expiry_trackers', function (Blueprint $table) {
            $table->index('next_notification_due_at', 'expiry_trackers_next_notification_due_at_index');
            $table->index('email_notifications_enabled', 'expiry_trackers_email_notifications_enabled_index');
        });

        Schema::table('smtp_profiles', function (Blueprint $table) {
            $table->index('is_default', 'smtp_profiles_is_default_index');
        });
    }

    public function down(): void
    {
        foreach ($this->softDeleteTables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->dropIndex("{$tbl}_deleted_at_index");
            });
        }

        foreach ($this->statusTables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->dropIndex("{$tbl}_status_index");
            });
        }

        Schema::table('expiry_trackers', function (Blueprint $table) {
            $table->dropIndex('expiry_trackers_next_notification_due_at_index');
            $table->dropIndex('expiry_trackers_email_notifications_enabled_index');
        });

        Schema::table('smtp_profiles', function (Blueprint $table) {
            $table->dropIndex('smtp_profiles_is_default_index');
        });
    }
};
