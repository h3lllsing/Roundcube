<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DataTypeConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    /** @var array<string, class-string> */
    private array $types = [];

    public function __construct()
    {
        $this->types = DataTypeConfig::importTypes();
    }

    /** @var string[] */
    private array $exclude = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function store(Request $request, string $type): JsonResponse
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);

        if (! isset($this->types[$type])) {
            return $this->message('Invalid import type', 404);
        }

        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $modelClass = $this->types[$type];
        $model = new $modelClass;
        $fillable = array_values(array_diff($model->getFillable(), $this->exclude));

        $exportTypes = DataTypeConfig::exportTypes();
        $exportColumns = isset($exportTypes[$type]) ? $exportTypes[$type]['columns'] : [];
        $validColumns = array_values(array_unique(array_merge($fillable, $exportColumns)));
        $validColumns = array_diff($validColumns, $this->exclude);

        try {
            $file = $request->file('file');
            $handle = fopen($file->getPathname(), 'r');
            if ($handle === false) {
                return response()->json(['message' => 'Could not open file'], 422); // @codeCoverageIgnore
            }

            $headers = fgetcsv($handle);
            if (! $headers) {
                fclose($handle);

                return response()->json(['message' => 'Import failed: empty or invalid CSV'], 422);
            }

            $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $headers);

            $mapped = [];
            foreach ($headers as $i => $header) {
                if (in_array($header, $validColumns)) {
                    $mapped[$i] = $header;
                }
            }

            $rows = [];
            while (($line = fgetcsv($handle)) !== false) {
                $line = array_map(fn ($v) => preg_replace('/^[=+\-@\t\r]/', '', trim((string) $v)), $line);
                if (count(array_filter($line)) === 0) {
                    continue;
                }
                $data = [];
                foreach ($mapped as $i => $col) {
                    if (isset($line[$i]) && $line[$i] !== '') {
                        $data[$col] = $line[$i];
                    }
                }
                if (in_array('user_id', $fillable)) {
                    $data['user_id'] = $request->user()->id;
                }
                $rows[] = $data;
            }

            fclose($handle);

            if (empty($rows)) {
                return response()->json(['message' => 'Import failed: CSV has no data rows'], 422);
            }

            $count = 0;
            DB::transaction(function () use ($modelClass, $fillable, $rows, &$count, $type, $request) {
                foreach ($rows as $data) {
                    if (in_array('user_id', $fillable)) {
                        $data['user_id'] = $data['user_id'] ?? $request->user()->id;
                    } else {
                        unset($data['user_id']);
                    }
                    if ($type === 'users' && isset($data['password'])) {
                        $data['password'] = Hash::make((string) $data['password']);
                    }
                    $safe = Arr::only($data, $fillable);
                    if ($type === 'vault') {
                        $entry = new $modelClass;
                        $entry->fill($safe);
                        $plainPassword = $data['encrypted_password'] ?? $data['password'] ?? '';
                        $entry->encryptPassword($plainPassword ?: '');
                        $entry->save();
                    } else {
                        $modelClass::create($safe);
                    }
                    $count++;
                }
            });

            return response()->json([
                'message' => "Imported {$count} record(s)",
                'data' => ['count' => $count],
            ], 201);
        } catch (\Exception $e) {
            Log::channel('api')->error('CSV import failed', ['type' => $type, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Import failed. Check your CSV format and try again.'], 422);
        }
    }
}
