<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MonitorService
{
    /** @return array{success: bool, status_code: int|null, response_time_ms: float|null, error: string|null} */
    public function ping(string $url): array
    {
        $start = microtime(true);
        try {
            $response = Http::timeout(10)->get($url);
            $elapsed = round((microtime(true) - $start) * 1000);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response_time_ms' => $elapsed,
                'error' => null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status_code' => null,
                'response_time_ms' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /** @return array{success: bool, error: string|null, valid: bool, days_remaining: int|null, issuer: string|null, valid_from?: string, valid_to?: string} */
    public function checkSsl(string $url): array
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return ['success' => false, 'error' => 'Invalid URL', 'valid' => false, 'days_remaining' => null, 'issuer' => null];
        }

        try {
            $context = stream_context_create(['ssl' => ['capture_peer_cert' => true, 'verify_peer' => env('MONITOR_SSL_VERIFY_PEER', true), 'verify_peer_name' => env('MONITOR_SSL_VERIFY_PEER_NAME', true)]]);
            $client = stream_socket_client('ssl://'.$host.':443', $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);

            if (! $client) {
                return ['success' => false, 'error' => $errstr, 'valid' => false, 'days_remaining' => null, 'issuer' => null];
            }

            $params = stream_context_get_params($client);
            $cert = $params['options']['ssl']['peer_certificate'];
            $certInfo = $this->parseSslCert($cert);
            fclose($client);

            if (! $certInfo) {
                return ['success' => false, 'error' => 'Failed to parse certificate', 'valid' => false, 'days_remaining' => null, 'issuer' => null];
            }

            $validFrom = $certInfo['validFrom_time_t'] ?? 0;
            $validTo = $certInfo['validTo_time_t'] ?? 0;
            $now = time();
            $daysRemaining = (int) ceil(($validTo - $now) / 86400);
            $issuer = $certInfo['issuer']['O'] ?? $certInfo['issuer']['CN'] ?? 'Unknown';

            return [
                'success' => true,
                'error' => null,
                'valid' => $validTo > $now,
                'days_remaining' => $daysRemaining,
                'issuer' => $issuer,
                'valid_from' => date('Y-m-d', $validFrom),
                'valid_to' => date('Y-m-d', $validTo),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'valid' => false, 'days_remaining' => null, 'issuer' => null];
        }
    }

    /** @return array<string, mixed>|false */
    protected function parseSslCert(mixed $cert): array|false
    {
        return openssl_x509_parse($cert);
    }

    /** @return array<string, mixed> */
    public function check(string $url): array
    {
        return [
            'url' => $url,
            'ping' => $this->ping($url),
            'ssl' => $this->checkSsl($url),
            'checked_at' => now()->toIso8601String(),
        ];
    }
}
