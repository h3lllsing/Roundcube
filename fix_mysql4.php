<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3310', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get password hash
    $stmt = $pdo->query("SELECT PASSWORD('')");
    $emptyHash = $stmt->fetchColumn();
    echo "Empty password hash: $emptyHash" . PHP_EOL;
    
    // Try setting password via mysql.user table hack - update the password column directly
    // In MariaDB, password column may be a different column name
    echo PHP_EOL . "Checking table structure..." . PHP_EOL;
    $stmt = $pdo->query("DESCRIBE mysql.user");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (preg_match('/password/i', $row['Field'])) {
            echo "Found: {$row['Field']} ({$row['Type']})" . PHP_EOL;
        }
    }
    
    // Use SET PASSWORD as root - this might work in skip-grant-tables
    echo PHP_EOL . "Trying SET PASSWORD..." . PHP_EOL;
    try {
        $pdo->exec("SET PASSWORD FOR 'root'@'localhost' = PASSWORD('')");
        echo "SUCCESS via SET PASSWORD" . PHP_EOL;
    } catch (Exception $e) {
        echo "FAIL: " . $e->getMessage() . PHP_EOL;
    }
    
    // Flush
    $pdo->exec("FLUSH PRIVILEGES");
    
    echo PHP_EOL . "Verifying..." . PHP_EOL;
    $stmt = $pdo->query("SELECT Host, User, authentication_string, password FROM mysql.user WHERE User='root'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $authStr = $row['authentication_string'] ?: '(empty)';
        $passStr = $row['password'] ?: (isset($row['Password']) ? ($row['Password'] ?: '(empty)') : 'N/A');
        echo "Host: {$row['Host']}, Auth: " . (strlen($authStr) > 30 ? substr($authStr, 0, 30) . '...' : $authStr) . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
