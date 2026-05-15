<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lcdatabase', 'root', '');
try {
    $result = $pdo->query("SHOW TABLES LIKE 'walkins%'");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    echo "Walkins tables: " . (empty($tables) ? "NONE" : implode(', ', $tables)) . "\n";
    
    if (in_array('walkins_logs', $tables)) {
        echo "\nwalkins_logs columns:\n";
        $result = $pdo->query("SHOW COLUMNS FROM walkins_logs");
        while ($col = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$col['Field']} ({$col['Type']})\n";
        }
    } else {
        echo "\nChecking all tables:\n";
        $result = $pdo->query("SHOW TABLES");
        $all = $result->fetchAll(PDO::FETCH_COLUMN);
        echo "Total tables: " . count($all) . "\n";
        foreach ($all as $table) {
            if (stripos($table, 'walkin') !== false || stripos($table, 'backup') !== false || stripos($table, 'log') !== false) {
                echo "  - $table\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
