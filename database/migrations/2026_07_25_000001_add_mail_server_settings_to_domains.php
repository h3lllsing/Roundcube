<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->string('imap_host', 255)->nullable()->after('notes');
            $table->unsignedSmallInteger('imap_port')->nullable()->after('imap_host');
            $table->string('imap_encryption', 10)->nullable()->after('imap_port');
            $table->string('smtp_host', 255)->nullable()->after('imap_encryption');
            $table->unsignedSmallInteger('smtp_port')->nullable()->after('smtp_host');
            $table->string('smtp_encryption', 10)->nullable()->after('smtp_port');
            $table->string('smtp_username', 255)->nullable()->after('smtp_encryption');
        });
    }

    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn([
                'imap_host', 'imap_port', 'imap_encryption',
                'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username',
            ]);
        });
    }
};
