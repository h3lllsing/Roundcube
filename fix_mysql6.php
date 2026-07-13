<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3310', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to skip-grant-tables server!" . PHP_EOL;
    
    // Check table users
    $stmt = $pdo->query("SELECT Host, User, plugin, authentication_string, Password FROM mysql.user WHERE User='root'");
    echo "Root users:" . PHP_EOL;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  Host: {$row['Host']}, Plugin: {$row['plugin']}, Auth: " . 
             (empty($row['authentication_string']) ? '(empty)' : '(' . strlen($row['authentication_string']) . ' chars)') . 
             ", Password: " . (empty($row['Password']) ? '(empty)' : '(' . strlen($row['Password']) . ' chars)') . PHP_EOL;
    }
    
    echo PHP_EOL;
    
    // Try direct login to skip_grant_tables - the UPDATE is not possible with skip_grant_tables
    // Let me try a different approach: use mysqladmin to connect when there's no grant checking
    
    // Try to directly update the password using SQL (without FLUSH)
    // Actually, with skip-grant-tables, we can SELECT but UPDATE/DELETE on mysql.user is blocked
    // Let me try using the Password() function to generate the correct hash
    echo "PASSWORD(''): " . var_export($pdo->query("SELECT PASSWORD('')")->fetchColumn(), true) . PHP_EOL;
    
    echo PHP_EOL . "We cannot fix the password using skip-grant-tables." . PHP_EOL;
    echo "We need to stop this instance, start normally, and use a different method." . PHP_EOL;
    
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . PHP_EOL;
}
