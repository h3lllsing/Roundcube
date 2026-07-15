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
        $orphans = [];

        foreach (self::TABLES as $table => $slug) {
            $moduleId = ModuleCache::idBySlug($slug);
            if (!$moduleId) {
                continue;
            }

            DB::table($table)
                ->whereNull('module_id')
                ->update(['module_id' => $moduleId]);

            // Report rows whose module_id points to a different module slug
            $wrongIds = DB::table($table . ' as t')
                ->join('modules as m', 't.module_id', '=', 'm.id')
                ->where('m.slug', '!=', $slug)
                ->whereNotNull('t.module_id')
                ->pluck('t.id');

            if ($wrongIds->isNotEmpty()) {
                $orphans[] = [
                    'table' => $table,
                    'expected_slug' => $slug,
                    'row_ids' => $wrongIds->toArray(),
                ];
            }
        }

        if ($orphans) {
            $msg = 'Backfill migration found rows with module_id pointing to a different module:';
            foreach ($orphans as $o) {
                $msg .= sprintf("\n  %s (expected slug: %s): row IDs %s", $o['table'], $o['expected_slug'], implode(', ', $o['row_ids']));
            }
            $msg .= "\nThese rows were NOT modified. Review manually.";
            trigger_error($msg, E_USER_WARNING);
        }

        Cache::increment('perms_generation');
        Cache::forget('modules_all_by_slug');
    }

    public function down(): void
    {
    }
};
