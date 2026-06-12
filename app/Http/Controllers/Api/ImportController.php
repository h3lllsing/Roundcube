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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    /** @var array<string, class-string> */
    private array $types = [
        'domains' => Domain::class,
        'hostings' => Hosting::class,
        'vps' => Vps::class,
        'voip' => Voip::class,
        'service-providers' => ServiceProvider::class,
        'domain-emails' => DomainEmail::class,
        'other-services' => OtherService::class,
        'expiry-trackers' => ExpiryTracker::class,
    ];

    /** @var string[] */
    private array $exclude = ['id', 'module_id', 'created_at', 'updated_at', 'deleted_at'];

    public function store(Request $request, string $type): \Illuminate\Http\JsonResponse
    {
        if (!isset($this->types[$type])) {
            return $this->message('Invalid import type', 404);
        }

        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $modelClass = $this->types[$type];
        $model = new $modelClass;
        /** @phpstan-ignore-next-line */
        $fillable = array_values(array_diff($model->getFillable(), $this->exclude));

        try {
            $file = $request->file('file');
            $handle = fopen($file->getPathname(), 'r');
            if ($handle === false) {
                return response()->json(['message' => 'Could not open file'], 422);
            }

            $headers = fgetcsv($handle);
            if (!$headers) {
                fclose($handle);
                return response()->json(['message' => 'Import failed: empty or invalid CSV'], 422);
            }

            $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $headers);

            $mapped = [];
            foreach ($headers as $i => $header) {
                if (in_array($header, $fillable)) {
                    $mapped[$i] = $header;
                }
            }

            $rows = [];
            while (($line = fgetcsv($handle)) !== false) {
                $line = array_map(fn ($v) => trim((string) $v), $line);
                if (count(array_filter($line)) === 0) {
                    continue;
                }
                $data = ['user_id' => $request->user()->id];
                foreach ($mapped as $i => $col) {
                    if (isset($line[$i]) && $line[$i] !== '') {
                        $data[$col] = $line[$i];
                    }
                }
                $rows[] = $data;
            }

            fclose($handle);

            if (empty($rows)) {
                return response()->json(['message' => 'Import failed: CSV has no data rows'], 422);
            }

            $count = 0;
            DB::transaction(function () use ($modelClass, $fillable, $rows, &$count) {
                foreach ($rows as $data) {
                    $safe = Arr::only($data, $fillable);
                    $safe['user_id'] = $data['user_id'];
                    $modelClass::create($safe);
                    $count++;
                }
            });

            return response()->json([
                'message' => "Imported {$count} record(s)",
                'data' => ['count' => $count],
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Import failed. Check your CSV format and try again.'], 422);
        }
    }
}
