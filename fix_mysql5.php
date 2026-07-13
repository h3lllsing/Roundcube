<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3310', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Trying to update Password column directly..." . PHP_EOL;
    try {
        // In MariaDB, the Password column is a longtext that stores the hash
        // Empty password hash is an empty string
        $stmt = $pdo->prepare("UPDATE mysql.user SET Password = '' WHERE User = 'root' AND Host = 'localhost'");
        $stmt->execute();
        echo "Rows affected: " . $stmt->rowCount() . PHP_EOL;
    } catch (Exception $e) {
        echo "FAIL on Password: " . $e->getMessage() . PHP_EOL;
    }
    
    // Also try authentication_string
    echo "Trying to update authentication_string column..." . PHP_EOL;
    try {
        $stmt = $pdo->prepare("UPDATE mysql.user SET authentication_string = '' WHERE User = 'root' AND Host = 'localhost'");
        $stmt->execute();
        echo "Rows affected: " . $stmt->rowCount() . PHP_EOL;
    } catch (Exception $e) {
        echo "FAIL on authentication_string: " . $e->getMessage() . PHP_EOL;
    }
    
    // Also try the plugin column - keep as mysql_native_password
    echo "Ensuring plugin is correct..." . PHP_EOL;
    try {
        $stmt = $pdo->prepare("UPDATE mysql.user SET plugin = 'mysql_native_password' WHERE User = 'root' AND Host = 'localhost'");
        $stmt->execute();
        echo "Rows affected: " . $stmt->rowCount() . PHP_EOL;
    } catch (Exception $e) {
        echo "FAIL: " . $e->getMessage() . PHP_EOL;
    }
    
    $pdo->exec("FLUSH PRIVILEGES");
    
    echo PHP_EOL . "Verifying..." . PHP_EOL;
    $stmt = $pdo->query("SELECT Host, User, plugin, authentication_string, Password FROM mysql.user WHERE User='root' AND Host='localhost'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "Plugin: {$row['plugin']}" . PHP_EOL;
        echo "authentication_string: " . (empty($row['authentication_string']) ? '(empty)' : '(set)') . PHP_EOL;
        echo "Password: " . (empty($row['Password']) ? '(empty)' : '(set)') . PHP_EOL;
    }
    
    echo PHP_EOL . "Done. Now stop this instance and restart MariaDB normally." . PHP_EOL;
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
