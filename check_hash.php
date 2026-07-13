<?php
// The current password hash is *36FAE6FE9EF1B411A50139B9B83AF999898FF6B5
// Let's see if this matches common passwords

// Connect to skip-grant-tables server
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3310', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test common passwords
    $common = ['', 'root', 'admin', 'password', 'mysql', '123456', 'xampp', 'P@ssw0rd', 'toor'];
    echo "Current hash: *36FAE6FE9EF1B411A50139B9B83AF999898FF6B5" . PHP_EOL;
    echo PHP_EOL;
    
    foreach ($common as $pwd) {
        $stmt = $pdo->prepare("SELECT PASSWORD(?) AS hash");
        $stmt->execute([$pwd]);
        $hash = $stmt->fetchColumn();
        echo "PASSWORD('$pwd') = $hash" . PHP_EOL;
        if ($hash === '*36FAE6FE9EF1B411A50139B9B83AF999898FF6B5') {
            echo "  ^^^ MATCH!" . PHP_EOL;
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo "Server might not be running. Trying to start one..." . PHP_EOL;
}
