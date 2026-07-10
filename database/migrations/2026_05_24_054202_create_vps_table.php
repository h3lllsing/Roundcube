<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->string('plan')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('os')->nullable();
            $table->integer('ram_mb')->nullable();
            $table->integer('disk_gb')->nullable();
            $table->integer('cpu_cores')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vps');
    }
};
