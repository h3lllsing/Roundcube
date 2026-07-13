<?php
// Try common passwords
$attempts = [
    'root' => ['', 'root', 'admin', 'mysql', 'password', '123456', 'toor', 'xampp', 'changeme'],
];

$success = false;
foreach ($attempts as $user => $passwords) {
    foreach ($passwords as $pass) {
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;port=3306', $user, $pass);
            echo "SUCCESS: $user : $pass" . PHP_EOL;
            $success = true;
            break 2;
        } catch (Exception $e) {
            // continue
        }
    }
}

if (!$success) {
    echo "NO MATCH FOUND" . PHP_EOL;
    
    // Also try localhost vs 127.0.0.1
    echo "Trying with localhost..." . PHP_EOL;
    try {
        $pdo = new PDO('mysql:host=localhost;port=3306', 'root', '');
        echo "SUCCESS: localhost / root / empty" . PHP_EOL;
    } catch (Exception $e) {
        echo "FAIL: " . $e->getMessage() . PHP_EOL;
    }
}
