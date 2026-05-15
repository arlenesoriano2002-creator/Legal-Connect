<?php
// Diagnostic script using direct database connection
// Read .env file manually
$env_file = __DIR__ . '/.env';
$config = [];
if (file_exists($env_file)) {
    foreach (file($env_file) as $line) {
        if (strpos($line, '=') !== false && strpos($line, 'DB_') === 0) {
            list($key, $val) = explode('=', $line, 2);
            $config[trim($key)] = trim($val);
        }
    }
}

$host = $config['DB_HOST'] ?? 'localhost';
$db = $config['DB_DATABASE'] ?? 'legalconnect_db';
$user = $config['DB_USERNAME'] ?? 'root';
$pass = $config['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    echo "=== DIAGNOSTIC REPORT ===\n\n";
    
    // 1. Check table exists
    echo "1. CHECKING TABLE EXISTENCE\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'concerns_inquiries_message'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Table 'concerns_inquiries_message' EXISTS\n\n";
    } else {
        echo "✗ Table 'concerns_inquiries_message' DOES NOT EXIST\n\n";
        exit(1);
    }
    
    // 2. Check columns
    echo "2. CHECKING TABLE COLUMNS\n";
    $stmt = $pdo->query("DESCRIBE concerns_inquiries_message");
    $Columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    echo "Total columns: " . count($Columns) . "\n";
    echo "Columns: " . implode(", ", $Columns) . "\n";
    if (in_array('subject', $Columns)) {
        echo "✓ 'subject' column EXISTS\n\n";
    } else {
        echo "✗ 'subject' column DOES NOT EXIST\n\n";
    }
    
    // 3. Count records
    echo "3. CHECKING DATA VOLUME\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM concerns_inquiries_message");
    $count = $stmt->fetchColumn();
    echo "Total records: " . $count . "\n\n";
    
    // 4. Sample data
    echo "4. CHECKING SAMPLE DATA (First 3 records)\n";
    $stmt = $pdo->query("SELECT * FROM concerns_inquiries_message LIMIT 3");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($records)) {
        echo "No records found!\n\n";
    } else {
        foreach ($records as $idx => $record) {
            echo "--- Record " . ($idx + 1) . " ---\n";
            echo "ID: " . $record['id'] . "\n";
            echo "Name: " . ($record['name'] ?? "NULL") . "\n";
            echo "Email: " . ($record['email'] ?? "NULL") . "\n";
            echo "Phone: " . ($record['phone'] ?? "NULL") . "\n";
            echo "Subject: " . ($record['subject'] ?? "NULL") . "\n";
            echo "Message: " . (isset($record['message']) ? substr($record['message'], 0, 50) . "..." : "NULL") . "\n";
            echo "Created: " . ($record['created_at'] ?? "NULL") . "\n";
            echo "\n";
        }
    }
    
    // 5. Subject stats
    echo "5. SUBJECT FIELD ANALYSIS\n";
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN subject IS NULL THEN 1 ELSE 0 END) as null_count,
        SUM(CASE WHEN subject = '' THEN 1 ELSE 0 END) as empty_count,
        SUM(CASE WHEN subject IS NOT NULL AND subject != '' THEN 1 ELSE 0 END) as filled_count
    FROM concerns_inquiries_message");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total records: " . $stats['total'] . "\n";
    echo "NULL subjects: " . $stats['null_count'] . "\n";
    echo "Empty subjects: " . $stats['empty_count'] . "\n";
    echo "Filled subjects: " . $stats['filled_count'] . "\n\n";
    
    echo "=== END DIAGNOSTIC ===\n";
    
} catch (PDOException $e) {
    echo "DATABASE ERROR: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
