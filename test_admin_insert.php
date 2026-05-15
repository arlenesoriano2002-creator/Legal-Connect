<?php
// Test the database insertion logic
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lcdatabase', 'root', '');

echo "=== Testing Admin Walkins Database Insertion ===\n\n";

// Simulate what the controller does
$dbPath = 'admin/test_' . date('YmdHis') . '.csv';
$testFileName = bin2hex(random_bytes(32)); // Simulating encrypted filename

echo "1. Testing updateOrCreate logic:\n";
echo "   DB Path: $dbPath\n";
echo "   File Name (encrypted): " . substr($testFileName, 0, 20) . "...\n";

try {
    // First, try direct insert (old method)
    $stmt = $pdo->prepare(
        "INSERT INTO walkins_logs (file_name, file_path, created_at) 
         VALUES (?, ?, NOW())"
    );
    $stmt->execute([$testFileName, $dbPath]);
    echo "   ✓ Direct INSERT successful\n";
    
    // Check if it was inserted
    $stmt = $pdo->prepare("SELECT * FROM walkins_logs WHERE file_path = ?");
    $stmt->execute([$dbPath]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($record) {
        echo "   ✓ Record verified in database:\n";
        echo "     ID: {$record['id']}\n";
        echo "     Path: {$record['file_path']}\n";
        echo "     Created: {$record['created_at']}\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Now test the getBackupLogs query
echo "\n2. Testing getBackupLogs query:\n";
try {
    $stmt = $pdo->prepare(
        "SELECT * FROM walkins_logs 
         WHERE file_path LIKE ? 
         ORDER BY created_at DESC"
    );
    $stmt->execute(['admin/%']);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Found " . count($logs) . " admin backup logs\n";
    foreach ($logs as $log) {
        echo "   - {$log['file_path']}\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
