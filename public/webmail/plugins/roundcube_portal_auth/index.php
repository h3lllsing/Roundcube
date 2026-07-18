<?php

class RoundcubePortalAuthPlugin extends \RainLoop\Plugins\AbstractPlugin
{
    const NAME     = 'Roundcube Portal Auth';
    const AUTHOR   = 'Roundcube Portal';
    const URL      = 'https://alphaspacepro.online';
    const VERSION  = '1.0';
    const RELEASE  = '2026-07-18';
    const REQUIRED = '2.0.0';
    const CATEGORY = 'Login';
    const DESCRIPTION = 'Auto-login from Roundcube Portal via signed URL token.';

    public function Init(): void
    {
        $this->addHook('json.parser.before', 'PluginJsonParserBefore');

        $this->addJsonHook('JsonAppDoLogin', 'PluginJsonAppDoLogin');
    }

    public function PluginJsonParserBefore(array &$aData): void
    {
        if (isset($_GET['rcp_token'])) {
            $token = $_GET['rcp_token'];
            $decrypted = json_decode(@base64_decode($token), true);

            if ($decrypted && isset($decrypted['email'], $decrypted['password'])) {
                $_POST['email'] = $decrypted['email'];
                if (isset($decrypted['imap_host'])) {
                    $_POST['imap_host'] = $decrypted['imap_host'];
                }
                if (isset($decrypted['imap_port'])) {
                    $_POST['imap_port'] = $decrypted['imap_port'];
                }
                if (isset($decrypted['imap_encryption'])) {
                    $_POST['imap_encryption'] = $decrypted['imap_encryption'];
                }
                if (isset($decrypted['smtp_host'])) {
                    $_POST['smtp_host'] = $decrypted['smtp_host'];
                }
                if (isset($decrypted['smtp_port'])) {
                    $_POST['smtp_port'] = $decrypted['smtp_port'];
                }
                if (isset($decrypted['smtp_encryption'])) {
                    $_POST['smtp_encryption'] = $decrypted['smtp_encryption'];
                }
                if (isset($decrypted['smtp_username'])) {
                    $_POST['smtp_username'] = $decrypted['smtp_username'];
                }
                if (isset($decrypted['smtp_password'])) {
                    $_POST['smtp_password'] = $decrypted['smtp_password'];
                }
                unset($_GET['rcp_token']);
            }
        }
    }

    public function PluginJsonAppDoLogin(): void
    {
        if (!empty($_GET['rcp_token'])) {
            $this->Manager()->Actions()->SetAuthToken($_GET['rcp_token']);
        }
    }
}
