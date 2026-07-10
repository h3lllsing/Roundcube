<?php

namespace App\Dashboard;

use App\Models\SmtpProfile;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServerHealthWidget
{
    public const SLUG = 'server-health';

    public function cacheTtl(): int
    {
        return 600;
    }

    public function data(User $user, ?array $accessibleIds = null): array
    {
        if (!$user->hasRole('super-admin')) {
            return [];
        }

        try {
            $diskFree = disk_free_space(storage_path());
            $diskTotal = disk_total_space(storage_path());
        } catch (\Throwable) {
            $diskFree = false;
            $diskTotal = false;
        }
        $diskUsedPct = ($diskTotal !== false && $diskTotal > 0) ? (int) round((1 - $diskFree / $diskTotal) * 100) : 0;

        $dbConnected = false;
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Throwable $e) {
            $dbConnected = false;
        }

        $mailStatus = $this->resolveMailStatus();

        return [
            'server_health' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()::VERSION,
                'app_version' => config('app.version', '—'),
                'cache_driver' => config('cache.default'),
                'session_driver' => config('session.driver'),
                'queue_driver' => config('queue.default'),
                'db_connection' => config('database.default'),
                'db_status' => $dbConnected ? 'Connected' : 'Unreachable',
                'disk_free' => $this->humanBytes($diskFree),
                'disk_total' => $this->humanBytes($diskTotal),
                'disk_used_pct' => $diskUsedPct,
                'scheduler_last_run' => Cache::get('scheduler:last_run', 'Never'),
                'mail_status' => $mailStatus,
            ],
        ];
    }

    private function resolveMailStatus(): string
    {
        $working = SmtpProfile::where('is_active', true)
            ->where('last_test_status', 'success')
            ->exists();
        if ($working) {
            return 'Working';
        }

        $untested = SmtpProfile::where('is_active', true)->exists();
        if ($untested) {
            return 'Configured (untested)';
        }

        $any = SmtpProfile::exists();
        if ($any) {
            return 'Configured (inactive)';
        }

        return 'Not configured';
    }

    private function humanBytes(int|float $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes, 1024));
        return round($bytes / (1024 ** $i), 1) . ' ' . $units[$i];
    }
}
