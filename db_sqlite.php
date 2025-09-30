<?php
require_once __DIR__ . '/config.php';

function get_pdo_connection(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // Use SQLite instead of MySQL
    $dbPath = __DIR__ . '/database.sqlite';
    $dsn = 'sqlite:' . $dbPath;
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, null, null, $options);
    return $pdo;
}
