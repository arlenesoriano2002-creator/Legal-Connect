<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lcdatabase', 'root', '');

echo "=== Admin Walkins Database Diagnostic ===\n\n";

// Check all walkins related tables
echo "1. All walkins-related tables:\n";
$result = $pdo->query("SHOW TABLES LIKE '%walkin%'");
$tables = $result->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    $countResult = $pdo->query("SELECT COUNT(*) FROM `$table`");
    $count = $countResult->fetchColumn();
    echo "   - $table: $count records\n";
}

// Check walkins_logs details
echo "\n2. Walkins_logs table structure:\n";
$result = $pdo->query("SHOW COLUMNS FROM walkins_logs");
while ($col = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "   - {$col['Field']} ({$col['Type']})\n";
}

// Check walkins_logs records
echo "\n3. Walkins_logs records (latest 10):\n";
$result = $pdo->query("SELECT * FROM walkins_logs ORDER BY created_at DESC LIMIT 10");
$logs = $result->fetchAll(PDO::FETCH_ASSOC);
if (empty($logs)) {
    echo "   (No records)\n";
} else {
    foreach ($logs as $log) {
        echo "   - ID: {$log['id']}\n";
        echo "     Path: {$log['file_path']}\n";
        echo "     Created: {$log['created_at']}\n";
    }
}

// Check branch values in walk-in tables
echo "\n4. Branch values in diffun_walkins (DISTINCT):\n";
try {
    $result = $pdo->query("SELECT DISTINCT branch FROM diffun_walkins");
    $branches = $result->fetchAll(PDO::FETCH_COLUMN);
    if (empty($branches)) {
        echo "   (No records)\n";
    } else {
        foreach ($branches as $branch) {
            echo "   - '" . ($branch ?? 'NULL') . "'\n";
        }
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n5. Branch values in cordon_walkins (DISTINCT):\n";
try {
    $result = $pdo->query("SELECT DISTINCT branch FROM cordon_walkins");
    $branches = $result->fetchAll(PDO::FETCH_COLUMN);
    if (empty($branches)) {
        echo "   (No records)\n";
    } else {
        foreach ($branches as $branch) {
            echo "   - '" . ($branch ?? 'NULL') . "'\n";
        }
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n6. Files in storage/app/public/walkin_logs_files/admin:\n";
$dir = __DIR__ . '/storage/app/public/walkin_logs_files/admin';
if (file_exists($dir)) {
    $files = glob($dir . '/*');
    if (empty($files)) {
        echo "   (No files)\n";
    } else {
        foreach ($files as $file) {
            echo "   - " . basename($file) . " (" . filesize($file) . " bytes)\n";
        }
    }
} else {
    echo "   Directory doesn't exist\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
?>
