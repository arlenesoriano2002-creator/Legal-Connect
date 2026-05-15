<?php
// Quick script to preview duplicated staff_notifications by staff_id+appointment_id
$host = '127.0.0.1';
$db   = 'lcdatabase';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $sql = "SELECT staff_id, appointment_id, COUNT(*) AS c FROM staff_notifications GROUP BY staff_id, appointment_id HAVING c > 1";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();
    if (count($rows) === 0) {
        echo "No duplicate staff_notifications found.\n";
        exit(0);
    }
    echo "Duplicates (staff_id | appointment_id | count):\n";
    foreach ($rows as $r) {
        echo $r['staff_id'] . ' | ' . $r['appointment_id'] . ' | ' . $r['c'] . "\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    exit(2);
}
