<?php
require_once __DIR__ . '/db/db.php';

try {
    $pdo = get_connection();
    // Test the connection
    echo "Connected to DB\n";
    $sql = file_get_contents(__DIR__ . '/db/migrations.sql');
    $pdo->exec($sql);
    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
