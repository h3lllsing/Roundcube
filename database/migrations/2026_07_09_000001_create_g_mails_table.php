<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('g_mails', function (Blueprint $table) {
            $table->id();
            $table->string('status')->nullable();
            $table->string('user_name')->nullable();
            $table->string('pseudo')->nullable();
            $table->string('emails_address')->nullable();
            $table->text('password')->nullable();
            $table->string('security_number')->nullable();
            $table->string('security_number_person')->nullable();
            $table->string('recovery_email')->nullable();
            $table->string('department')->nullable();
            $table->string('assigned')->nullable();
            $table->text('user_remarks')->nullable();
            $table->text('comments')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('g_mails');
    }
};
