<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3311', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to skip-grant-tables server on port 3311!" . PHP_EOL;
    
    // Check current users
    echo PHP_EOL . "=== Current root user ===" . PHP_EOL;
    $stmt = $pdo->query("SELECT Host, User, plugin, LENGTH(authentication_string) as auth_len, LENGTH(Password) as pwd_len FROM mysql.user WHERE User='root'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo implode(', ', $row) . PHP_EOL;
    }
    
    // Try SET PASSWORD
    echo PHP_EOL . "=== Trying to reset password ===" . PHP_EOL;
    try {
        $pdo->exec("SET PASSWORD FOR 'root'@'localhost' = PASSWORD('')");
        echo "SUCCESS: SET PASSWORD worked!" . PHP_EOL;
    } catch (Exception $e) {
        echo "SET PASSWORD failed: " . $e->getMessage() . PHP_EOL;
    }
    
    // Try FLUSH PRIVILEGES and then ALTER USER
    echo "Trying ALTER USER..." . PHP_EOL;
    try {
        $pdo->exec("ALTER USER 'root'@'localhost' IDENTIFIED BY ''");
        echo "SUCCESS: ALTER USER worked!" . PHP_EOL;
    } catch (Exception $e) {
        echo "ALTER USER failed: " . $e->getMessage() . PHP_EOL;
    }
    
    // Try direct UPDATE
    echo "Trying UPDATE mysql.user..." . PHP_EOL;
    try {
        $pdo->exec("UPDATE mysql.user SET Password = '' WHERE User = 'root' AND Host = 'localhost'");
        echo "SUCCESS: UPDATE Password worked!" . PHP_EOL;
    } catch (Exception $e) {
        echo "UPDATE Password failed: " . $e->getMessage() . PHP_EOL;
    }
    
    try {
        $pdo->exec("UPDATE mysql.user SET authentication_string = '' WHERE User = 'root' AND Host = 'localhost'");
        echo "SUCCESS: UPDATE authentication_string worked!" . PHP_EOL;
    } catch (Exception $e) {
        echo "UPDATE authentication_string failed: " . $e->getMessage() . PHP_EOL;
    }
    
    // Flush privileges
    try {
        $pdo->exec("FLUSH PRIVILEGES");
        echo "FLUSH PRIVILEGES succeeded" . PHP_EOL;
    } catch (Exception $e) {
        echo "FLUSH PRIVILEGES failed: " . $e->getMessage() . PHP_EOL;
    }
    
    echo PHP_EOL . "=== Verification ===" . PHP_EOL;
    $stmt = $pdo->query("SELECT Host, User, plugin, LENGTH(authentication_string) as auth_len, LENGTH(Password) as pwd_len FROM mysql.user WHERE User='root'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo implode(', ', $row) . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "CONNECTION FAILED: " . $e->getMessage() . PHP_EOL;
}
