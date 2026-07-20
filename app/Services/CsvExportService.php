<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    public function export(Collection $rows, array $headers, string $filename): StreamedResponse
    {
        $callback = function () use ($rows, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                $values = [];
                foreach ($headers as $header) {
                    $values[] = $row[$header] ?? '';
                }
                fputcsv($handle, $values);
            }

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }
}
