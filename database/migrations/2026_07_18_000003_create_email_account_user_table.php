<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_account_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_account_id')->constrained('email_accounts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('can_send')->default(true);
            $table->boolean('can_receive')->default(true);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['email_account_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_account_user');
    }
};
