<?php
require_once __DIR__ . '/db_sqlite.php';

try {
    $pdo = get_pdo_connection();
    
    // Read and execute the SQLite schema
    $schema = file_get_contents(__DIR__ . '/schema_sqlite.sql');
    $statements = explode(';', $schema);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "✅ SQLite database setup completed successfully!\n";
    echo "📁 Database file: " . __DIR__ . "/database.sqlite\n";
    
    // Test the connection
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "👥 Users in database: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Error setting up database: " . $e->getMessage() . "\n";
}
