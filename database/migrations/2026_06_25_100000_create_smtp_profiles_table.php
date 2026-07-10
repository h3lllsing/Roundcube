<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smtp_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sender_name');
            $table->string('sender_email');
            $table->string('reply_to_email')->nullable();
            $table->string('smtp_host');
            $table->integer('smtp_port')->default(587);
            $table->string('smtp_encryption')->nullable();
            $table->string('smtp_username');
            $table->text('smtp_password');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(100);
            $table->timestamp('last_tested_at')->nullable();
            $table->string('last_test_status', 20)->nullable();
            $table->text('last_test_error')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('is_active', 'sp_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_profiles');
    }
};
