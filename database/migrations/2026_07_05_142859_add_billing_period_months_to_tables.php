<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['expiry_trackers', 'domains', 'hostings', 'vps', 'voip', 'domain_emails', 'other_services'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->unsignedTinyInteger('billing_period_months')->default(12)->after('cost');
            });
        }
    }

    public function down(): void
    {
        $tables = ['expiry_trackers', 'domains', 'hostings', 'vps', 'voip', 'domain_emails', 'other_services'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('billing_period_months');
            });
        }
    }
};
