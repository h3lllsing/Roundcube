<?php

namespace App\Services;

use App\Models\User;
use App\Support\DataTypeConfig;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\LazyCollection;
use Laravel\Sanctum\PersonalAccessToken;

class ExportService
{
    private array $types;

    public function __construct()
    {
        $this->types = DataTypeConfig::exportTypes();
    }

    public function export(User $user, string $type): array
    {
        if (! $user->hasRole('super-admin')) {
            return ['error' => 'Forbidden.'];
        }

        if (! isset($this->types[$type])) {
            return ['error' => 'Invalid export type.'];
        }

        $cfg = $this->types[$type];
        $modelClass = $cfg['model'];
        $columns = $cfg['columns'];

        $query = $modelClass::orderBy('id');

        if ($type === 'tokens') {
            $query = PersonalAccessToken::where('tokenable_id', $user->id)
                ->where('tokenable_type', get_class($user))
                ->orderBy('id');
        } elseif ($user->hasRole('super-admin')) {
            // Super-admin: no scope — export all records
        } elseif (isset($cfg['module_slug'])) {
            $accessibleIds = $user->getAccessibleModuleIds('export');
            if (empty($accessibleIds)) {
                return ['error' => 'Forbidden.'];
            }
            $query->whereIn('module_id', $accessibleIds);
        } else {
            $query->where('user_id', $user->id);
        }

        $filename = $type.'-'.now()->format('Y-m-d-His').'.csv';

        $csv = $this->toCsv($query->select($columns)->lazy(), $columns);

        activity()->event('exported')
            ->causedBy($user)
            ->withProperties([
                'type' => $type,
                'filename' => $filename,
                'module_slug' => $cfg['module_slug'] ?? null,
            ])
            ->log('Exported CSV: '.$type);

        return compact('csv', 'filename');
    }

    private function toCsv(LazyCollection $rows, array $columns): string
    {
        $out = fopen('php://temp', 'r+');
        if ($out === false) {
            return '';
        }
        fputcsv($out, $columns);
        foreach ($rows as $row) {
            $data = [];
            foreach ($columns as $col) {
                $v = $row->$col;
                $data[] = is_array($v) ? json_encode($v) : $this->sanitizeCsvValue($v);
            }
            fputcsv($out, $data);
        }
        rewind($out);
        $content = stream_get_contents($out);
        fclose($out);

        return $content;
    }

    private function sanitizeCsvValue(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if (preg_match('/^[=+\-@]/', $value)) {
            return "\t".$value;
        }

        return $value;
    }

    public function isValidType(string $type): bool
    {
        return isset($this->types[$type]);
    }
}
