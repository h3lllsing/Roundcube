<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->cascadeOnDelete();
            $table->string('email', 255)->unique();
            $table->text('password');
            $table->string('imap_host', 255)->default('localhost');
            $table->unsignedSmallInteger('imap_port')->default(993);
            $table->enum('imap_encryption', ['ssl', 'tls', 'none'])->default('ssl');
            $table->string('smtp_host', 255)->nullable();
            $table->unsignedSmallInteger('smtp_port')->nullable();
            $table->enum('smtp_encryption', ['ssl', 'tls', 'none'])->nullable();
            $table->string('smtp_username', 255)->nullable();
            $table->text('smtp_password')->nullable();
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->boolean('sync_enabled')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_accounts');
    }
};
