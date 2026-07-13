<?php
// Let's try to connect via the actual MariaDB with socket method
// On Windows, MariaDB might accept connections through the named pipe

// Try connecting via localhost with named pipe
// First, let's see if the mysql.sock file exists  
$sockPath = 'D:/xampp/mysql/mysql.sock';
echo "Socket exists: " . (file_exists($sockPath) ? 'yes' : 'no') . PHP_EOL;

// Try using unix_socket connection method
try {
    // On Windows, this connects through shared memory / named pipe
    $pdo = new PDO('mysql:unix_socket=' . $sockPath . ';dbname=mysql', 'root', '');
    echo "Connected via unix_socket!" . PHP_EOL;
    $stmt = $pdo->query("SELECT CURRENT_USER()");
    echo "User: " . $stmt->fetchColumn() . PHP_EOL;
} catch (Exception $e) {
    echo "Socket connect failed: " . $e->getMessage() . PHP_EOL;
}
