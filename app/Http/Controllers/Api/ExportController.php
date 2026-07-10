<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DataTypeConfig;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;

class ExportController extends Controller
{
    /** @param array<string, array{model: class-string, columns: string[], admin?: bool, module_slug?: string}> */
    private array $types = [];

    public function __construct()
    {
        $this->types = DataTypeConfig::exportTypes();
    }

    public function export(Request $request, string $type): Response|JsonResponse
    {
        if (! $request->user()->hasRole('super-admin')) {
            return $this->message('Forbidden', 403);
        }

        if (! isset($this->types[$type])) {
            return $this->message('Invalid export type', 404);
        }

        $cfg = $this->types[$type];
        $modelClass = $cfg['model'];
        $columns = $cfg['columns'];

        $query = $modelClass::orderBy('id');
        if ($type === 'tokens') {
            $query = PersonalAccessToken::where('tokenable_id', $request->user()->id)
                ->where('tokenable_type', get_class($request->user()))
                ->orderBy('id');
        }
        $rows = $query->select($columns)->lazy()->map(fn ($m) => $m->only($columns))->toArray();

        $csv = $this->toCsv(array_merge([$columns], $rows));

        $filename = $type.'-'.now()->format('Y-m-d-His').'.csv';

        return ResponseFacade::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
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

    /** @param array<int, array<int, mixed>> $data */
    private function toCsv(array $data): string
    {
        $out = fopen('php://temp', 'r+');
        if ($out === false) {
            return '';
        }
        foreach ($data as $row) {
            $row = array_map(fn ($v) => is_array($v) ? json_encode($v) : $this->sanitizeCsvValue($v), $row);
            fputcsv($out, $row);
        }
        rewind($out);
        $content = stream_get_contents($out);
        fclose($out);

        return $content;
    }
}
