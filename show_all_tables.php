<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=legalconnect_db', 'root', '');
    
    // Check all tables
    $result = $pdo->query("SHOW TABLES");
    $allTables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "=== Database Tables ===\n";
    echo "Total tables: " . count($allTables) . "\n";
    
    // Find walk-in related tables
    $walkTables = array_filter($allTables, function($t) {
        return stripos($t, 'walkin') !== false || stripos($t, 'backup') !== false || stripos($t, 'logs') !== false;
    });
    
    echo "\nWalk-in/Backup/Logs related tables:\n";
    if (empty($walkTables)) {
        echo "NONE FOUND\n";
    } else {
        foreach ($walkTables as $table) {
            echo "  - $table\n";
        }
    }
    
    // Show all tables (first 20)
    echo "\nAll databases (first 30):\n";
    $count = 0;
    foreach ($allTables as $table) {
        echo "  " . $count++ . ": $table\n";
        if ($count >= 30) break;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
