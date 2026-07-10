<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hostings', function (Blueprint $table) {
            $table->string('domain_ip', 45)->nullable()->after('domain');
            $table->string('mail_domain_ip', 45)->nullable()->after('domain_ip');
            $table->string('cpanel_ip', 45)->nullable()->after('mail_domain_ip');
        });
    }

    public function down(): void
    {
        Schema::table('hostings', function (Blueprint $table) {
            $table->dropColumn(['domain_ip', 'mail_domain_ip', 'cpanel_ip']);
        });
    }
};
