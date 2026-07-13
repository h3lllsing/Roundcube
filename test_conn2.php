<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "SUCCESS: Connected with empty password on port 3306!" . PHP_EOL;
    $stmt = $pdo->query("SELECT CURRENT_USER(), VERSION()");
    $row = $stmt->fetch(PDO::FETCH_NUM);
    echo "User: {$row[0]}, Version: {$row[1]}" . PHP_EOL;
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . PHP_EOL;
}
