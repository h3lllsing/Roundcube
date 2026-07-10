<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $serviceTables = [
        'domains', 'hostings', 'vps', 'voip',
        'service_providers', 'domain_emails', 'other_services', 'expiry_trackers',
    ];

    private array $expiryTables = [
        'domains', 'hostings', 'vps', 'voip',
        'service_providers', 'domain_emails', 'other_services',
    ];

    private array $activeTables = [
        'features', 'modules', 'webhooks',
    ];

    public function up(): void
    {
        foreach ($this->serviceTables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->index(['module_id', 'status'], "{$tbl}_module_id_status_index");
            });
        }

        foreach ($this->expiryTables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->index('expiry_date', "{$tbl}_expiry_date_index");
            });
        }

        foreach ($this->activeTables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->index('is_active', "{$tbl}_is_active_index");
            });
        }

        Schema::table('activity_log', function (Blueprint $table) {
            $table->index('causer_id', 'activity_log_causer_id_index');
        });
    }

    public function down(): void
    {
        foreach ($this->serviceTables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->dropIndex("{$tbl}_module_id_status_index");
            });
        }

        foreach ($this->expiryTables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->dropIndex("{$tbl}_expiry_date_index");
            });
        }

        foreach ($this->activeTables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->dropIndex("{$tbl}_is_active_index");
            });
        }

        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex('activity_log_causer_id_index');
        });
    }
};
