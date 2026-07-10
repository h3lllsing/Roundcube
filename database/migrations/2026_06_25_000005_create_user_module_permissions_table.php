<?php

use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_module_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Module::class)->constrained()->cascadeOnDelete();
            $table->boolean('can_create')->nullable();
            $table->boolean('can_read')->nullable();
            $table->boolean('can_update')->nullable();
            $table->boolean('can_delete')->nullable();
            $table->boolean('can_approve')->nullable();
            $table->boolean('can_export')->nullable();
            $table->boolean('can_reveal')->nullable();
            $table->boolean('can_import')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_module_permissions');
    }
};
