<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Voip;
use App\Models\Vps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    /** @var array<string, array{model: class-string, columns: string[]}> */
    private array $types = [
        'domains' => [
            'model' => Domain::class, 'columns' => ['name', 'registrar', 'registration_date', 'expiry_date', 'auto_renew', 'cost', 'status', 'dns_servers', 'notes', 'created_at'],
        ],
        'hostings' => [
            'model' => Hosting::class, 'columns' => ['name', 'provider', 'plan', 'domain', 'start_date', 'expiry_date', 'cost', 'status', 'notes', 'created_at'],
        ],
        'vps' => [
            'model' => Vps::class, 'columns' => ['name', 'provider', 'plan', 'ip_address', 'os', 'ram_mb', 'disk_gb', 'cpu_cores', 'cost', 'start_date', 'expiry_date', 'status', 'notes', 'created_at'],
        ],
        'voip' => [
            'model' => Voip::class, 'columns' => ['name', 'provider', 'phone_number', 'type', 'username', 'cost', 'expiry_date', 'status', 'notes', 'created_at'],
        ],
        'service-providers' => [
            'model' => ServiceProvider::class, 'columns' => ['name', 'type', 'website', 'cost', 'status', 'notes', 'created_at'],
        ],
        'domain-emails' => [
            'model' => DomainEmail::class, 'columns' => ['email', 'provider', 'domain_id', 'storage_mb', 'cost', 'expiry_date', 'status', 'notes', 'created_at'],
        ],
        'other-services' => [
            'model' => OtherService::class, 'columns' => ['name', 'service_type', 'website', 'cost', 'expiry_date', 'status', 'notes', 'created_at'],
        ],
        'expiry-trackers' => [
            'model' => ExpiryTracker::class, 'columns' => ['name', 'expiry_date', 'cost', 'status', 'notes', 'created_at'],
        ],
    ];

    public function export(Request $request, string $type): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
    {
        if (!isset($this->types[$type])) {
            return $this->message('Invalid export type', 404);
        }

        $user = $request->user();
        $cfg = $this->types[$type];
        $modelClass = $cfg['model'];
        $columns = $cfg['columns'];

        $query = $modelClass::orderBy('id');
        if (!$user->hasRole('super-admin')) {
            $query->where('user_id', $user->id);
        }
        $rows = $query->get($columns)->toArray();

        $csv = $this->toCsv(array_merge([$columns], $rows));

        $filename = $type . '-' . now()->format('Y-m-d-His') . '.csv';

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /** @param array<int, array<int, mixed>> $data */
    private function toCsv(array $data): string
    {
        $out = fopen('php://temp', 'r+');
        if ($out === false) {
            return '';
        }
        foreach ($data as $row) {
            $row = array_map(fn($v) => is_array($v) ? json_encode($v) : $v, $row);
            fputcsv($out, $row);
        }
        rewind($out);
        $content = stream_get_contents($out);
        fclose($out);
        return $content;
    }
}
