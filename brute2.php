<?php
$passwords = ['', 'root', 'mysql', 'admin', 'xampp', 'password'];

foreach ($passwords as $pass) {
    try {
        $start = microtime(true);
        $pdo = new PDO("mysql:host=127.0.0.1;port=3306", 'root', $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 1,
        ]);
        $elapsed = microtime(true) - $start;
        echo "SUCCESS: '$pass' (took " . round($elapsed, 3) . "s)" . PHP_EOL;
        exit;
    } catch (Exception $e) {
        $elapsed = microtime(true) - $start;
        echo "FAIL: '$pass' - " . $e->getMessage() . " ({$elapsed}s)" . PHP_EOL;
    }
}
