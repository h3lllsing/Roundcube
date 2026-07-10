<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expiry_trackers', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }

    public function down(): void
    {
        Schema::table('expiry_trackers', function (Blueprint $table) {
            $table->text('password')->nullable()->after('username');
        });
    }
};
