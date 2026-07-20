<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('module_role_permissions');
        Schema::dropIfExists('user_module_permissions');
        Schema::dropIfExists('features');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('privilege_role');
        Schema::dropIfExists('privileges');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('tyro_audit_logs');
        Schema::dropIfExists('roles');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
    }
};
