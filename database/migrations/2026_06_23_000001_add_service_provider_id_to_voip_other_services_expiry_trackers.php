<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voip', function (Blueprint $table) {
            $table->foreignId('service_provider_id')->nullable()->after('module_id')->constrained('service_providers')->nullOnDelete();
            $table->text('password')->nullable()->after('username');
            $table->string('dashboard_url')->nullable()->after('password');
        });

        $providers = DB::table('voip')->whereNotNull('provider')->distinct()->pluck('provider');
        foreach ($providers as $providerName) {
            $existing = DB::table('service_providers')->where('name', $providerName)->first();
            if (! $existing) {
                DB::table('service_providers')->insert([
                    'name' => $providerName,
                    'type' => 'voip',
                    'provider' => $providerName,
                    'website' => null,
                    'user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $serviceProvider = DB::table('service_providers')->where('name', $providerName)->first();
            DB::table('voip')->where('provider', $providerName)
                ->update(['service_provider_id' => $serviceProvider->id]);
        }

        Schema::table('voip', function (Blueprint $table) {
            $table->dropColumn('provider');
        });

        Schema::table('other_services', function (Blueprint $table) {
            $table->foreignId('service_provider_id')->nullable()->after('module_id')->constrained('service_providers')->nullOnDelete();
            $table->string('username')->nullable()->after('service_type');
            $table->text('password')->nullable()->after('username');
            $table->string('login_url')->nullable()->after('password');
        });

        $providers = DB::table('other_services')->whereNotNull('provider')->distinct()->pluck('provider');
        foreach ($providers as $providerName) {
            $existing = DB::table('service_providers')->where('name', $providerName)->first();
            if (! $existing) {
                DB::table('service_providers')->insert([
                    'name' => $providerName,
                    'type' => 'other_service',
                    'provider' => $providerName,
                    'website' => null,
                    'user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $serviceProvider = DB::table('service_providers')->where('name', $providerName)->first();
            DB::table('other_services')->where('provider', $providerName)
                ->update(['service_provider_id' => $serviceProvider->id]);
        }

        Schema::table('other_services', function (Blueprint $table) {
            $table->dropColumn('provider');
        });

        Schema::table('expiry_trackers', function (Blueprint $table) {
            $table->foreignId('service_provider_id')->nullable()->after('module_id')->constrained('service_providers')->nullOnDelete();
            $table->string('username')->nullable()->after('name');
            $table->text('password')->nullable()->after('username');
            $table->string('login_url')->nullable()->after('password');
        });

        $providers = DB::table('expiry_trackers')->whereNotNull('provider')->distinct()->pluck('provider');
        foreach ($providers as $providerName) {
            $existing = DB::table('service_providers')->where('name', $providerName)->first();
            if (! $existing) {
                DB::table('service_providers')->insert([
                    'name' => $providerName,
                    'type' => 'expiry_tracker',
                    'provider' => $providerName,
                    'website' => null,
                    'user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $serviceProvider = DB::table('service_providers')->where('name', $providerName)->first();
            DB::table('expiry_trackers')->where('provider', $providerName)
                ->update(['service_provider_id' => $serviceProvider->id]);
        }

        Schema::table('expiry_trackers', function (Blueprint $table) {
            $table->dropColumn('provider');
        });
    }

    public function down(): void
    {
        Schema::table('voip', function (Blueprint $table) {
            $table->dropForeign(['service_provider_id']);
            $table->dropColumn('service_provider_id');
            $table->dropColumn('password');
            $table->dropColumn('dashboard_url');
            $table->string('provider')->nullable();
        });

        Schema::table('other_services', function (Blueprint $table) {
            $table->dropForeign(['service_provider_id']);
            $table->dropColumn('service_provider_id');
            $table->dropColumn('username');
            $table->dropColumn('password');
            $table->dropColumn('login_url');
            $table->string('provider')->nullable();
        });

        Schema::table('expiry_trackers', function (Blueprint $table) {
            $table->dropForeign(['service_provider_id']);
            $table->dropColumn('service_provider_id');
            $table->dropColumn('username');
            $table->dropColumn('password');
            $table->dropColumn('login_url');
            $table->string('provider')->nullable();
        });
    }
};
