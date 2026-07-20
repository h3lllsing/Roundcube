<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_audits', function (Blueprint $table) {
            $table->string('hash_chain', 64)->nullable()->after('event');
            $table->index('hash_chain');
        });
    }

    public function down(): void
    {
        Schema::table('login_audits', function (Blueprint $table) {
            $table->dropIndex(['hash_chain']);
            $table->dropColumn('hash_chain');
        });
    }
};
