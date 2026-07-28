<?php
echo "PHP: " . PHP_VERSION . "\n";
echo "ZTS: " . (PHP_ZTS ? 'TS' : 'NTS') . "\n";
echo "Arch: " . PHP_INT_SIZE * 8 . "bit\n";
echo "IMAP: " . (function_exists('imap_open') ? 'YES' : 'NO') . "\n";
echo "Extension dir: " . PHP_EXTENSION_DIR . "\n";
