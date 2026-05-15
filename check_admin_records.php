<?php
$pdo = new PDO('mysql:host=127.0.0.1; dbname=lcdatabase', 'root', '');

echo "=== Checking Admin Records in walkins_logs ===\n\n";

$stmt = $pdo->prepare(
    "SELECT id, file_path, created_at FROM walkins_logs 
     WHERE file_path LIKE 'admin/%' 
     ORDER BY created_at DESC"
);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Admin records found: " . count($logs) . "\n\n";

if (count($logs) > 0) {
    foreach ($logs as $log) {
        echo "ID: {$log['id']}\n";
        echo "Path: {$log['file_path']}\n";
        echo "Created: {$log['created_at']}\n\n";
    }
    echo "✓ Admin records ARE in the database!\n";
} else {
    echo "✗ No admin records found. Checking all records:\n";
    $stmt = $pdo->query("SELECT id, file_path FROM walkins_logs ORDER BY created_at DESC LIMIT 5");
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($all as $log) {
        echo "  - {$log['file_path']}\n";
    }
}
?>
