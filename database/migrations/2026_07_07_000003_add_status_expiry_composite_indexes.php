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
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->index(['status', 'expiry_date'], "{$table}_status_expiry_index");
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropIndex("{$table}_status_expiry_index");
            });
        }
    }
};
