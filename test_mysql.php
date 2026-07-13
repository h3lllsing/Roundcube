<?php
try {
    new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    echo "Connected OK";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
