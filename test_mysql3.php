<?php
// Try with the .env credentials
$host = '127.0.0.1';
$user = 'whizzweb_whizzweb_opspilot';
$pass = "J2M(7^8O~1,k0G0m0c";
try {
    new PDO("mysql:host=$host;port=3306", $user, $pass);
    echo "SUCCESS with .env credentials" . PHP_EOL;
} catch (Exception $e) {
    echo "FAIL with .env creds: " . $e->getMessage() . PHP_EOL;
}
