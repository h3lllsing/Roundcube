<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'domains', 'hostings', 'vps', 'voip',
        'service_providers', 'domain_emails', 'other_services', 'expiry_trackers',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('monitoring_url')->nullable()->after('notes');
                $t->timestamp('last_ping_at')->nullable()->after('monitoring_url');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['monitoring_url', 'last_ping_at']);
            });
        }
    }
};
