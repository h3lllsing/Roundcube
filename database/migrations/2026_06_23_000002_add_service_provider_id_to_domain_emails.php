<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domain_emails', function (Blueprint $table) {
            $table->foreignId('service_provider_id')->nullable()->after('module_id')->constrained('service_providers')->nullOnDelete();
        });

        $providers = DB::table('domain_emails')->whereNotNull('provider')->distinct()->pluck('provider');
        foreach ($providers as $providerName) {
            $existing = DB::table('service_providers')->where('name', $providerName)->first();
            if (! $existing) {
                DB::table('service_providers')->insert([
                    'name' => $providerName,
                    'type' => 'domain_email',
                    'provider' => $providerName,
                    'website' => null,
                    'user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $serviceProvider = DB::table('service_providers')->where('name', $providerName)->first();
            DB::table('domain_emails')->where('provider', $providerName)
                ->update(['service_provider_id' => $serviceProvider->id]);
        }

        Schema::table('domain_emails', function (Blueprint $table) {
            $table->dropColumn('provider');
        });
    }

    public function down(): void
    {
        Schema::table('domain_emails', function (Blueprint $table) {
            $table->dropForeign(['service_provider_id']);
            $table->dropColumn('service_provider_id');
            $table->string('provider')->nullable();
        });
    }
};
