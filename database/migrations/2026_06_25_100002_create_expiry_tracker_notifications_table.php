<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expiry_tracker_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expiry_tracker_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('smtp_profile_id')->nullable()->index();
            $table->string('sender_email');
            $table->integer('reminder_day');
            $table->string('recipient_email');
            $table->string('recipient_type', 50);
            $table->string('trigger_source', 20)->default('cron');
            $table->string('status', 20)->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['expiry_tracker_id', 'reminder_day', 'recipient_email'], 'etn_tracker_day_email_idx');
            $table->index('status', 'etn_status_idx');
            $table->index('smtp_profile_id', 'etn_smtp_profile_idx');
            $table->index('trigger_source', 'etn_trigger_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expiry_tracker_notifications');
    }
};
