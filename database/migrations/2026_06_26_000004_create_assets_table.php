<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag')->unique();
            $table->foreignId('category_id')->constrained('asset_categories')->cascadeOnDelete();
            $table->foreignId('type_id')->constrained('asset_types')->cascadeOnDelete();
            $table->string('serial_number')->nullable();
            $table->string('status')->default('available');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('asset_locations')->nullOnDelete();
            $table->string('department')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('return_date')->nullable();
            $table->string('condition')->nullable();
            $table->json('specifications')->nullable();
            $table->text('notes')->nullable();
            $table->string('primary_image')->nullable();
            $table->foreignId('vault_entry_id')->nullable()->constrained('password_vault')->nullOnDelete();
            $table->string('qr_identifier')->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('assigned_to');
            $table->index('location_id');
            $table->index('condition');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
