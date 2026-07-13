<?php
$dsn = 'mysql:host=127.0.0.1;port=3308';
$pdo = new PDO($dsn, 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check current users
echo "=== Current MySQL users ===" . PHP_EOL;
$stmt = $pdo->query("SELECT Host, User, plugin, authentication_string FROM mysql.user WHERE User IN ('root', 'pma', '') ORDER BY User, Host");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Host: {$row['Host']}, User: {$row['User']}, Plugin: {$row['plugin']}, Auth: " . (empty($row['authentication_string']) ? '(empty)' : '(set)') . PHP_EOL;
}

// Check what root user exists
echo PHP_EOL . "=== Creating/replacing root user with mysql_native_password ===" . PHP_EOL;

// For MariaDB 10.4+, we need to use SET PASSWORD or ALTER USER
// First try to see what exists
try {
    // Set password for existing users
    $pdo->exec("ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('')");
    echo "Set root@localhost password empty" . PHP_EOL;
} catch (Exception $e) {
    echo "Error setting root@localhost: " . $e->getMessage() . PHP_EOL;
}

try {
    $pdo->exec("ALTER USER 'root'@'127.0.0.1' IDENTIFIED VIA mysql_native_password USING PASSWORD('')");
    echo "Set root@127.0.0.1 password empty" . PHP_EOL;
} catch (Exception $e) {
    echo "Error setting root@127.0.0.1: " . $e->getMessage() . PHP_EOL;
}

try {
    // Also ensure root can connect from localhost (some MariaDB versions)
    $pdo->exec("CREATE USER IF NOT EXISTS 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('')");
    $pdo->exec("GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION");
    echo "Created root@localhost" . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

try {
    $pdo->exec("CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED VIA mysql_native_password USING PASSWORD('')");
    $pdo->exec("GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION");
    echo "Created root@127.0.0.1" . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

$pdo->exec("FLUSH PRIVILEGES");
echo PHP_EOL . "=== After fix ===" . PHP_EOL;

$stmt = $pdo->query("SELECT Host, User, plugin FROM mysql.user WHERE User='root' ORDER BY Host");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Host: {$row['Host']}, User: {$row['User']}, Plugin: {$row['plugin']}" . PHP_EOL;
}
