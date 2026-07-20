<?php

namespace App\Services;

class SmtpAutoDiscover
{
    public function discoverAll(string $email): array
    {
        $domain = $this->extractDomain($email);
        if (! $domain) {
            return ['error' => 'Invalid email address.'];
        }

        $smtp = $this->discoverSmtp($domain, $email);
        $imap = $this->discoverImap($domain, $email);

        return [
            'imap_host' => $imap['host'] ?? null,
            'imap_port' => $imap['port'] ?? null,
            'imap_encryption' => $imap['encryption'] ?? null,
            'smtp_host' => $smtp['host'] ?? null,
            'smtp_port' => $smtp['port'] ?? null,
            'smtp_encryption' => $smtp['encryption'] ?? null,
            'smtp_username' => $email,
        ];
    }

    private function discoverSmtp(string $domain, string $email): array
    {
        $srv = $this->getSrvRecord('_submission._tcp.' . $domain);
        if ($srv) {
            return [
                'host' => $srv['target'],
                'port' => $srv['port'],
                'encryption' => $srv['port'] === 465 ? 'ssl' : 'tls',
            ];
        }

        $srv = $this->getSrvRecord('_smtps._tcp.' . $domain);
        if ($srv) {
            return [
                'host' => $srv['target'],
                'port' => $srv['port'],
                'encryption' => 'ssl',
            ];
        }

        $mxHost = $this->getMxHost($domain);
        if ($mxHost) {
            return [
                'host' => $mxHost,
                'port' => 587,
                'encryption' => 'tls',
            ];
        }

        return [
            'host' => 'mail.' . $domain,
            'port' => 587,
            'encryption' => 'tls',
        ];
    }

    private function discoverImap(string $domain, string $email): array
    {
        $srv = $this->getSrvRecord('_imaps._tcp.' . $domain);
        if ($srv) {
            return [
                'host' => $srv['target'],
                'port' => $srv['port'],
                'encryption' => 'ssl',
            ];
        }

        $srv = $this->getSrvRecord('_imap._tcp.' . $domain);
        if ($srv) {
            return [
                'host' => $srv['target'],
                'port' => $srv['port'],
                'encryption' => 'tls',
            ];
        }

        $mxHost = $this->getMxHost($domain);
        if ($mxHost) {
            return [
                'host' => $mxHost,
                'port' => 993,
                'encryption' => 'ssl',
            ];
        }

        return [
            'host' => 'mail.' . $domain,
            'port' => 993,
            'encryption' => 'ssl',
        ];
    }

    private function getSrvRecord(string $service): ?array
    {
        $records = @dns_get_record($service, DNS_SRV);
        if (! empty($records)) {
            usort($records, fn ($a, $b) => $a['pri'] - $b['pri']);
            return $records[0];
        }
        return null;
    }

    private function extractDomain(string $email): ?string
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }
        return substr(strstr($email, '@') ?: '', 1) ?: null;
    }

    private function getMxHost(string $domain): ?string
    {
        $mx = @dns_get_record($domain, DNS_MX);
        if (! empty($mx)) {
            usort($mx, fn ($a, $b) => $a['pri'] - $b['pri']);
            return rtrim($mx[0]['target'], '.');
        }

        $a = @dns_get_record($domain, DNS_A);
        if (! empty($a)) {
            return $a[0]['ip'];
        }

        return null;
    }
}
