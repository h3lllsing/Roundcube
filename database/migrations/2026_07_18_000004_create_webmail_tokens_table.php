<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webmail_tokens', function (Blueprint $table) {
            $table->string('token', 128)->primary();
            $table->foreignId('email_account_id')->constrained('email_accounts')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->index('email_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webmail_tokens');
    }
};
