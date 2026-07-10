<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('version')->default('1.0');
            $table->boolean('is_protected')->default(true);
            $table->boolean('is_dangerous')->default(false);
            $table->json('permissions_json');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_templates');
    }
};
