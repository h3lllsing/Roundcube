<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expiry_trackers', function (Blueprint $table) {
            $table->boolean('email_notifications_enabled')->default(false)->after('notes');
            $table->foreignId('smtp_profile_id')->nullable()->constrained('smtp_profiles')->nullOnDelete()->after('email_notifications_enabled');
            $table->json('notify_days_before')->default('[30,15,7,1]')->after('smtp_profile_id');
            $table->boolean('notify_on_expiry_day')->default(false)->after('notify_days_before');
            $table->boolean('notify_assigned_user')->default(true)->after('notify_on_expiry_day');
            $table->boolean('notify_admins')->default(false)->after('notify_assigned_user');
            $table->json('notify_custom_emails')->nullable()->after('notify_admins');
            $table->timestamp('last_notification_sent_at')->nullable()->after('notify_custom_emails');
            $table->date('next_notification_due_at')->nullable()->after('last_notification_sent_at');
            $table->foreignId('disabled_by')->nullable()->constrained('users')->nullOnDelete()->after('next_notification_due_at');
            $table->timestamp('disabled_at')->nullable()->after('disabled_by');
            $table->string('disable_reason')->nullable()->after('disabled_at');
        });
    }

    public function down(): void
    {
        Schema::table('expiry_trackers', function (Blueprint $table) {
            $table->dropForeign(['smtp_profile_id']);
            $table->dropForeign(['disabled_by']);
            $table->dropColumn([
                'smtp_profile_id',
                'email_notifications_enabled',
                'notify_days_before',
                'notify_on_expiry_day',
                'notify_assigned_user',
                'notify_admins',
                'notify_custom_emails',
                'last_notification_sent_at',
                'next_notification_due_at',
                'disabled_by',
                'disabled_at',
                'disable_reason',
            ]);
        });
    }
};
