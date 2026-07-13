<?php
// Check if we can connect using MariaDB specific config
// Or maybe the issue is auth_plugin mismatch

// First let's see what auth plugins are available
echo "PDO drivers: " . implode(', ', PDO::getAvailableDrivers()) . PHP_EOL;

// The issue might be that MariaDB 10.4 uses ed25519 or caching_sha2_password
// while PHP mysqlnd expects mysql_native_password

// Let's try with pdo_mysql directly
try {
    $dsn = 'mysql:host=127.0.0.1;port=3306';
    $pdo = new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "Connected!" . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Code: " . $e->getCode() . PHP_EOL;
}
