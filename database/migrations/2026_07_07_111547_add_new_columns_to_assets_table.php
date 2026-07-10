<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('asset_tag');
            $table->string('model')->nullable()->after('brand');
            $table->string('processor')->nullable()->after('model');
            $table->string('ram')->nullable()->after('processor');
            $table->string('storage')->nullable()->after('ram');
            $table->string('os')->nullable()->after('storage');
            $table->string('reporting_authority')->nullable()->after('assigned_to');
            $table->string('premises')->nullable()->after('location_id');
            $table->string('headphone')->nullable()->after('status');
            $table->text('additional_equipments')->nullable()->after('headphone');
            $table->text('additional_comments')->nullable()->after('description');
            $table->string('anydesk_id')->nullable()->after('additional_comments');
            $table->string('anydesk_password')->nullable()->after('anydesk_id');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'brand', 'model', 'processor', 'ram', 'storage', 'os',
                'reporting_authority', 'premises', 'headphone',
                'additional_equipments', 'additional_comments',
                'anydesk_id', 'anydesk_password',
            ]);
        });
    }
};
