<?php

namespace App\Services;

use App\Support\DataTypeConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ImportService
{
    private array $types;
    private array $exclude = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function __construct()
    {
        $this->types = DataTypeConfig::importTypes();
    }

    public function import(UploadedFile $file, string $type): array
    {
        if (! isset($this->types[$type])) {
            return ['error' => 'Invalid import type.'];
        }

        $modelClass = $this->types[$type];
        $model = new $modelClass;
        $fillable = array_values(array_diff($model->getFillable(), $this->exclude));

        $exportTypes = DataTypeConfig::exportTypes();
        $exportColumns = isset($exportTypes[$type]) ? $exportTypes[$type]['columns'] : [];
        $validColumns = array_values(array_unique(array_merge($fillable, $exportColumns)));
        $validColumns = array_diff($validColumns, $this->exclude);

        $handle = fopen($file->getPathname(), 'r');
        if ($handle === false) {
            return ['error' => 'Could not open file.'];
        }

        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);
            return ['error' => 'Import failed: empty or invalid CSV.'];
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
                $data['user_id'] = Auth::id();
            }
            $rows[] = $data;
        }

        fclose($handle);

        if (empty($rows)) {
            return ['error' => 'Import failed: CSV has no data rows.'];
        }

        $count = 0;
        try {
            DB::transaction(function () use ($modelClass, $fillable, $rows, &$count, $type) {
                foreach ($rows as $data) {
                    if (in_array('user_id', $fillable)) {
                        $data['user_id'] = $data['user_id'] ?? Auth::id();
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
        } catch (\Throwable $e) {
            Log::error('Import failed', ['type' => $type, 'error' => $e->getMessage()]);
            return ['error' => 'Import failed. Check your CSV format and try again.'];
        }

        activity()->event('imported')
            ->causedBy(Auth::user())
            ->withProperties([
                'type' => $type,
                'count' => $count,
            ])
            ->log('Imported '.$count.' '.$type.' via CSV');

        return ['success' => "Imported {$count} record(s).", 'count' => $count];
    }

    public function getTypes(): array
    {
        return array_keys($this->types);
    }

    public function isValidType(string $type): bool
    {
        return isset($this->types[$type]);
    }
}
