<?php
// Let's check if maybe the MySQL connection needs different host
// In XAMPP on Windows, sometimes there's an issue with auth plugin
// Try all possible connection methods

// 1. Vanilla PDO
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    echo "1. SUCCESS: PDO 127.0.0.1 root/empty" . PHP_EOL;
} catch (Exception $e) {
    echo "1. FAIL: " . $e->getMessage() . PHP_EOL;
}

// 2. mysqli
try {
    $mysqli = mysqli_connect('127.0.0.1', 'root', '', 'mysql', 3306);
    echo "2. SUCCESS: mysqli 127.0.0.1 root/empty" . PHP_EOL;
} catch (Exception $e) {
    echo "2. FAIL: " . $e->getMessage() . PHP_EOL;
}

// 3. PDO with localhost
try {
    $pdo = new PDO('mysql:host=localhost;port=3306', 'root', '');
    echo "3. SUCCESS: PDO localhost root/empty" . PHP_EOL;
} catch (Exception $e) {
    echo "3. FAIL: " . $e->getMessage() . PHP_EOL;
}

// 4. PDO with explicit charset
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8', 'root', '');
    echo "4. SUCCESS: PDO 127.0.0.1 root/empty utf8" . PHP_EOL;
} catch (Exception $e) {
    echo "4. FAIL: " . $e->getMessage() . PHP_EOL;
}
