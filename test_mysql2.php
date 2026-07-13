<?php
// Try common XAMPP passwords
$attempts = [
    ['127.0.0.1', 'root', ''],
    ['127.0.0.1', 'root', 'mysql'],
    ['127.0.0.1', 'root', 'root'],
    ['localhost', 'root', ''],
    ['127.0.0.1', 'root', 'password'],
];

foreach ($attempts as [$host, $user, $pass]) {
    try {
        new PDO("mysql:host=$host;port=3306", $user, $pass);
        echo "SUCCESS: host=$host user=$user pass=$pass" . PHP_EOL;
    } catch (Exception $e) {
        echo "FAIL: host=$host user=$user pass=$pass - " . $e->getMessage() . PHP_EOL;
    }
}
