<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_providers', fn (Blueprint $t) => $t->renameColumn('notes', 'description'));
        Schema::table('domains', fn (Blueprint $t) => $t->renameColumn('notes', 'description'));
        Schema::table('hostings', fn (Blueprint $t) => $t->renameColumn('notes', 'description'));
        Schema::table('vps', fn (Blueprint $t) => $t->renameColumn('notes', 'description'));
        Schema::table('voip', fn (Blueprint $t) => $t->renameColumn('notes', 'description'));
        Schema::table('domain_emails', fn (Blueprint $t) => $t->renameColumn('notes', 'description'));
        Schema::table('other_services', fn (Blueprint $t) => $t->renameColumn('notes', 'description'));
        Schema::table('expiry_trackers', fn (Blueprint $t) => $t->renameColumn('notes', 'description'));
        Schema::table('assets', fn (Blueprint $t) => $t->renameColumn('notes', 'description'));
    }

    public function down(): void
    {
        Schema::table('service_providers', fn (Blueprint $t) => $t->renameColumn('description', 'notes'));
        Schema::table('domains', fn (Blueprint $t) => $t->renameColumn('description', 'notes'));
        Schema::table('hostings', fn (Blueprint $t) => $t->renameColumn('description', 'notes'));
        Schema::table('vps', fn (Blueprint $t) => $t->renameColumn('description', 'notes'));
        Schema::table('voip', fn (Blueprint $t) => $t->renameColumn('description', 'notes'));
        Schema::table('domain_emails', fn (Blueprint $t) => $t->renameColumn('description', 'notes'));
        Schema::table('other_services', fn (Blueprint $t) => $t->renameColumn('description', 'notes'));
        Schema::table('expiry_trackers', fn (Blueprint $t) => $t->renameColumn('description', 'notes'));
        Schema::table('assets', fn (Blueprint $t) => $t->renameColumn('description', 'notes'));
    }
};
