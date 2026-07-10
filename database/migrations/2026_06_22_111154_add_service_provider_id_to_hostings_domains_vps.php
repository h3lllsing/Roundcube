<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hostings', function (Blueprint $table) {
            $table->foreignId('service_provider_id')->nullable()->after('module_id')->constrained('service_providers')->nullOnDelete();
        });

        $providers = DB::table('hostings')->whereNotNull('provider')->distinct()->pluck('provider');
        foreach ($providers as $providerName) {
            $existing = DB::table('service_providers')->where('name', $providerName)->first();
            if (! $existing) {
                DB::table('service_providers')->insert([
                    'name' => $providerName,
                    'type' => 'hosting',
                    'provider' => $providerName,
                    'website' => null,
                    'user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $serviceProvider = DB::table('service_providers')->where('name', $providerName)->first();
            DB::table('hostings')->where('provider', $providerName)
                ->update(['service_provider_id' => $serviceProvider->id]);
        }

        Schema::table('hostings', function (Blueprint $table) {
            $table->dropColumn('provider');
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->foreignId('service_provider_id')->nullable()->after('hosting_id')->constrained('service_providers')->nullOnDelete();
        });

        $providers = DB::table('domains')->whereNotNull('registrar')->distinct()->pluck('registrar');
        foreach ($providers as $providerName) {
            $existing = DB::table('service_providers')->where('name', $providerName)->first();
            if (! $existing) {
                DB::table('service_providers')->insert([
                    'name' => $providerName,
                    'type' => 'domain',
                    'provider' => $providerName,
                    'website' => null,
                    'user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $serviceProvider = DB::table('service_providers')->where('name', $providerName)->first();
            DB::table('domains')->where('registrar', $providerName)
                ->update(['service_provider_id' => $serviceProvider->id]);
        }

        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn('registrar');
        });

        Schema::table('vps', function (Blueprint $table) {
            $table->foreignId('service_provider_id')->nullable()->after('module_id')->constrained('service_providers')->nullOnDelete();
        });

        $providers = DB::table('vps')->whereNotNull('provider')->distinct()->pluck('provider');
        foreach ($providers as $providerName) {
            $existing = DB::table('service_providers')->where('name', $providerName)->first();
            if (! $existing) {
                DB::table('service_providers')->insert([
                    'name' => $providerName,
                    'type' => 'vps',
                    'provider' => $providerName,
                    'website' => null,
                    'user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $serviceProvider = DB::table('service_providers')->where('name', $providerName)->first();
            DB::table('vps')->where('provider', $providerName)
                ->update(['service_provider_id' => $serviceProvider->id]);
        }

        Schema::table('vps', function (Blueprint $table) {
            $table->dropColumn('provider');
        });
    }

    public function down(): void
    {
        Schema::table('hostings', function (Blueprint $table) {
            $table->dropForeign(['service_provider_id']);
            $table->dropColumn('service_provider_id');
            $table->string('provider')->nullable();
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->dropForeign(['service_provider_id']);
            $table->dropColumn('service_provider_id');
            $table->string('registrar')->nullable();
        });

        Schema::table('vps', function (Blueprint $table) {
            $table->dropForeign(['service_provider_id']);
            $table->dropColumn('service_provider_id');
            $table->string('provider')->nullable();
        });
    }
};
