<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_role_permissions', function (Blueprint $table) {
            $table->boolean('can_import')->default(false)->after('can_export');
        });
    }

    public function down(): void
    {
        Schema::table('module_role_permissions', function (Blueprint $table) {
            $table->dropColumn('can_import');
        });
    }
};
