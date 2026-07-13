<?php
// Try many passwords
$passwords = [
    '', 'root', 'admin', 'mysql', 'password', '123456', '12345', '123456789',
    'password1', 'p@ssword', 'changeme', 'toor', 'xampp', 'xampp123', 'admin123',
    'pass', 'admin123', 'root123', '1234', '12345678', 'abc123', 'test', 
    'P@ssw0rd', 'Passw0rd', 'p@ssw0rd', 'Pa$$w0rd', 
    'opspilot', 'whizzweb', 'admin1234', 'root1234',
    'guest', 'user', 'sa', 'administrator',
    'welcome', 'monkey', 'dragon', 'master', 'qwerty',
    'letmein', 'sunshine', 'princess', 'football', 'iloveyou',
    '111111', '000000', '1234', '1234567890',
];

$maxAttempts = 0;
foreach ($passwords as $pass) {
    try {
        $pdo = new PDO("mysql:host=127.0.0.1;port=3306", 'root', $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 2,
        ]);
        echo "SUCCESS: Password = '$pass'" . PHP_EOL;
        $maxAttempts++;
        $stmt = $pdo->query("SELECT CURRENT_USER(), VERSION()");
        $row = $stmt->fetch(PDO::FETCH_NUM);
        echo "User: {$row[0]}, Version: {$row[1]}" . PHP_EOL;
        break;
    } catch (Exception $e) {
        $maxAttempts++;
        if ($maxAttempts > count($passwords)) break;
        continue;
    }
}
