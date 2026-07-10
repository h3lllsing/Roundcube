<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expiry_trackers', function (Blueprint $table) {
            $table->nullableMorphs('trackable');
        });
    }

    public function down(): void
    {
        Schema::table('expiry_trackers', function (Blueprint $table) {
            $table->dropIndex(['trackable_type_trackable_id_index']);
            $table->dropColumn(['trackable_type', 'trackable_id']);
        });
    }
};
