<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('voip', function (Blueprint $table) {
            $table->json('extensions')->nullable()->after('name');
            $table->text('extension_password')->nullable();
            $table->string('server_ip')->nullable();
            $table->string('direction')->nullable();
            $table->string('number_status')->nullable();
            $table->string('outbound_code')->nullable();
            $table->text('team_details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('voip', function (Blueprint $table) {
            $table->dropColumn(['extensions', 'extension_password', 'server_ip', 'direction', 'number_status', 'outbound_code', 'team_details']);
        });
    }
};
