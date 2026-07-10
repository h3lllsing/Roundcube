<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->index('subject_type', 'activity_log_subject_type_index');
        });

        Schema::table('login_audits', function (Blueprint $table) {
            $table->index(['email', 'event'], 'login_audits_email_event_index');
        });

        Schema::table('webhooks', function (Blueprint $table) {
            $table->index('user_id', 'webhooks_user_id_index');
        });

        Schema::table('smtp_profiles', function (Blueprint $table) {
            $table->index(['is_default', 'is_active'], 'smtp_profiles_default_active_index');
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->fullText('content', 'notes_content_fulltext');
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropIndex('notes_content_fulltext');
        });

        Schema::table('smtp_profiles', function (Blueprint $table) {
            $table->dropIndex('smtp_profiles_default_active_index');
        });

        Schema::table('webhooks', function (Blueprint $table) {
            $table->dropIndex('webhooks_user_id_index');
        });

        Schema::table('login_audits', function (Blueprint $table) {
            $table->dropIndex('login_audits_email_event_index');
        });

        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex('activity_log_subject_type_index');
        });
    }
};
