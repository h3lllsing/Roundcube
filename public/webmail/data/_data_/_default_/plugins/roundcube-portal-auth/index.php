<?php

class RoundcubePortalAuthPlugin extends \RainLoop\Plugins\AbstractPlugin
{
    const NAME     = 'Roundcube Portal Auth';
    const AUTHOR   = 'Roundcube Portal';
    const URL      = 'https://alphaspacepro.online';
    const VERSION  = '1.2';
    const RELEASE  = '2026-07-19';
    const REQUIRED = '2.0.0';
    const CATEGORY = 'Login';
    const DESCRIPTION = 'Auto-login from Roundcube Portal via SSO with per-account IMAP settings.';

    public function Init(): void
    {
        $this->addHook('imap.before-connect', 'PluginImapBeforeConnect');
        $this->addHook('smtp.before-connect', 'PluginSmtpBeforeConnect');
        $this->addHook('imap.before-login', 'PluginImapBeforeLogin');
        $this->addHook('smtp.before-login', 'PluginSmtpBeforeLogin');
    }

    protected function getImapSettings(string $email): ?array
    {
        $file = sys_get_temp_dir() . '/sm_imap_' . md5($email) . '.json';
        if (!is_file($file)) {
            return null;
        }

        $data = json_decode(file_get_contents($file), true);
        if (!$data) {
            return null;
        }

        $age = time() - ($data['created_at'] ?? 0);
        if ($age >= 600) {
            @unlink($file);
            return null;
        }

        return $data;
    }

    public function PluginImapBeforeConnect($account, $imapClient, $settings): void
    {
        $email = $account->Email();
        $data = $this->getImapSettings($email);
        if ($data && !empty($data['imap_host'])) {
            $settings->host = $data['imap_host'];
            $settings->port = (int)$data['imap_port'];
            switch ($data['imap_encryption'] ?? 'ssl') {
                case 'ssl':
                case 'tls':
                    $settings->type = \MailSo\Net\Enumerations\ConnectionSecurityType::SSL;
                    break;
                case 'starttls':
                    $settings->type = \MailSo\Net\Enumerations\ConnectionSecurityType::STARTTLS;
                    break;
                default:
                    $settings->type = \MailSo\Net\Enumerations\ConnectionSecurityType::NONE;
            }
        }
    }

    public function PluginSmtpBeforeConnect($account, $smtpClient, $settings): void
    {
        $email = $account->Email();
        $data = $this->getImapSettings($email);
        if ($data && !empty($data['smtp_host'])) {
            $settings->host = $data['smtp_host'];
            $settings->port = (int)$data['smtp_port'];
            switch ($data['smtp_encryption'] ?? 'tls') {
                case 'ssl':
                case 'tls':
                    $settings->type = \MailSo\Net\Enumerations\ConnectionSecurityType::SSL;
                    break;
                case 'starttls':
                    $settings->type = \MailSo\Net\Enumerations\ConnectionSecurityType::STARTTLS;
                    break;
                default:
                    $settings->type = \MailSo\Net\Enumerations\ConnectionSecurityType::NONE;
            }
            if (!empty($data['smtp_username'])) {
                $settings->username = $data['smtp_username'];
            }
        }
    }

    public function PluginImapBeforeLogin($account, $client, $settings): void
    {
    }

    public function PluginSmtpBeforeLogin($account, $client, $settings): void
    {
        $email = $account->Email();
        $data = $this->getImapSettings($email);
        if ($data) {
            if (!empty($data['smtp_password'])) {
                $settings->passphrase = new \SnappyMail\SensitiveString($data['smtp_password']);
            } elseif (!empty($data['password'])) {
                $settings->passphrase = new \SnappyMail\SensitiveString($data['password']);
            }
            if (!empty($data['smtp_username'])) {
                $settings->username = $data['smtp_username'];
            }
        }
    }
}
