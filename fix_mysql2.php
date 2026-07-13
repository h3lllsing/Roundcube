<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3310', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Current MySQL users ===" . PHP_EOL;
    $stmt = $pdo->query("SELECT Host, User, plugin FROM mysql.user WHERE User IN ('root', 'pma', '') ORDER BY User, Host");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Host: {$row['Host']}, User: {$row['User']}, Plugin: {$row['plugin']}" . PHP_EOL;
    }
    
    echo PHP_EOL . "=== Setting root@localhost to mysql_native_password with empty password ===" . PHP_EOL;
    
    // Fix root @ localhost
    $pdo->exec("ALTER USER 'root'@'localhost' IDENTIFIED BY ''");
    echo "Set root@localhost password empty" . PHP_EOL;
    
    // Create root @ 127.0.0.1 too
    try {
        $pdo->exec("ALTER USER 'root'@'127.0.0.1' IDENTIFIED BY ''");
        echo "Set root@127.0.0.1 password empty" . PHP_EOL;
    } catch (Exception $e) {
        // User might not exist, create it
        $pdo->exec("CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY ''");
        $pdo->exec("GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION");
        echo "Created root@127.0.0.1" . PHP_EOL;
    }
    
    $pdo->exec("FLUSH PRIVILEGES");
    
    echo PHP_EOL . "=== After fix ===" . PHP_EOL;
    $stmt = $pdo->query("SELECT Host, User, plugin FROM mysql.user WHERE User='root' ORDER BY Host");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Host: {$row['Host']}, User: {$row['User']}, Plugin: {$row['plugin']}" . PHP_EOL;
    }
    
    echo PHP_EOL . "SUCCESS - Root password reset!" . PHP_EOL;
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
