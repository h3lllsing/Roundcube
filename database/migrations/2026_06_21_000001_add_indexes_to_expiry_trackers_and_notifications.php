<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expiry_trackers', function (Blueprint $table) {
            $table->index('expiry_date');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['type', 'notifiable_id'], 'notifications_type_notifiable_id_index');
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::table('expiry_trackers', function (Blueprint $table) {
            $table->dropIndex(['expiry_date']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_type_notifiable_id_index');
            $table->dropIndex(['read_at']);
        });
    }
};
