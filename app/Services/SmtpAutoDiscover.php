<?php

namespace App\Services;

class SmtpAutoDiscover
{
    public function discover(string $email): array
    {
        $domain = $this->extractDomain($email);
        if (! $domain) {
            return ['error' => 'Invalid email address.'];
        }

        $mxHost = $this->getMxHost($domain);
        if (! $mxHost) {
            return ['error' => "No mail server found for {$domain}."];
        }

        return [
            'host' => $mxHost,
            'port' => 587,
            'encryption' => 'tls',
            'username' => $email,
        ];
    }

    private function extractDomain(string $email): ?string
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }
        return substr(strstr($email, '@'), 1);
    }

    private function getMxHost(string $domain): ?string
    {
        $mx = dns_get_record($domain, DNS_MX);
        if (! empty($mx)) {
            usort($mx, fn ($a, $b) => $a['pri'] - $b['pri']);
            return $mx[0]['target'];
        }

        $a = dns_get_record($domain, DNS_A);
        if (! empty($a)) {
            return $a[0]['ip'];
        }

        return null;
    }
}
