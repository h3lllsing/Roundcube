<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expiry_tracker_notifications', function (Blueprint $table) {
            $table->foreign('smtp_profile_id')->references('id')->on('smtp_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expiry_tracker_notifications', function (Blueprint $table) {
            $table->dropForeign(['smtp_profile_id']);
        });
    }
};
