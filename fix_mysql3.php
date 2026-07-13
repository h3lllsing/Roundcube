<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3310', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Current root user details ===" . PHP_EOL;
    $stmt = $pdo->query("SELECT Host, User, plugin, authentication_string, password FROM mysql.user WHERE User='root'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        foreach ($row as $k => $v) {
            echo "$k: " . (empty($v) ? '(empty)' : (strlen($v) > 40 ? substr($v, 0, 40) . '...' : $v)) . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== Updating password directly ===" . PHP_EOL;
    
    // In MariaDB 10.4+, we need to update the authentication_string with a proper hash
    // Empty password in mysql_native_password is represented by empty string
    $stmt = $pdo->prepare("UPDATE mysql.user SET authentication_string = '' WHERE User = 'root' AND Host = 'localhost'");
    $stmt->execute();
    echo "Updated root@localhost password to empty" . PHP_EOL;
    
    // Also ensure 127.0.0.1 entry exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM mysql.user WHERE User='root' AND Host='127.0.0.1'");
    $count = $stmt->fetchColumn();
    if ($count == 0) {
        // In MariaDB, INSERT into mysql.user requires all columns
        // Let's just ensure we can connect via localhost
        echo "No root@127.0.0.1 entry exists, but localhost should work for both" . PHP_EOL;
    }
    
    $pdo->exec("FLUSH PRIVILEGES");
    
    echo PHP_EOL . "SUCCESS!" . PHP_EOL;
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
