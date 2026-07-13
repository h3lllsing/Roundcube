<?php
// Try connecting now that MariaDB is restarted
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    echo "SUCCESS: Connected as root with empty password!" . PHP_EOL;
    $stmt = $pdo->query("SELECT CURRENT_USER(), VERSION()");
    $row = $stmt->fetch(PDO::FETCH_NUM);
    echo "User: " . $row[0] . PHP_EOL;
    echo "Version: " . $row[1] . PHP_EOL;
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . PHP_EOL;
}
