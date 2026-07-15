<?php

use App\Helpers\ModuleCache;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TABLES = [
        'g_mails'           => 'g-mails',
        'hostings'          => 'hostings',
        'vps'               => 'vps',
        'voip'              => 'voip',
        'domain_emails'     => 'domain-emails',
        'service_providers' => 'service-providers',
        'other_services'    => 'other-services',
        'assets'            => 'assets',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table => $slug) {
            $moduleId = ModuleCache::idBySlug($slug);
            if (!$moduleId) {
                continue;
            }

            // Backfill null module_ids
            DB::table($table)
                ->whereNull('module_id')
                ->update(['module_id' => $moduleId]);

            // Correct non-null module_ids that point to a different module slug
            $wrongIds = DB::table($table . ' as t')
                ->join('modules as m', 't.module_id', '=', 'm.id')
                ->where('m.slug', '!=', $slug)
                ->whereNotNull('t.module_id')
                ->pluck('t.id');

            if ($wrongIds->isNotEmpty()) {
                DB::table($table)
                    ->whereIn('id', $wrongIds)
                    ->update(['module_id' => $moduleId]);
            }
        }

        Cache::increment('perms_generation');
        Cache::forget('modules_all_by_slug');
    }

    public function down(): void
    {
    }
};
