<?php
$host='127.0.0.1'; $db='lcdatabase'; $user='root'; $pass='';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
try{
    $pdo = new PDO($dsn,$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
}catch(Exception $e){
    echo "DB connect error: ".$e->getMessage()."\n"; exit(1);
}

$terms = [
    '%test received%',
    '%test message%',
    '%test received 1/31%',
    '%test received 1/31/26%'
];
$params = [];
$w = [];
foreach($terms as $i=>$t){
    $w[] = "message LIKE :t$i";
    $params[":t$i"] = $t;
}

$sql = "SELECT id,sender_email,receiver_email,subject,message,created_at,timestamp_normalized,message_id FROM chattbl WHERE (".implode(' OR ', $w).") AND created_at BETWEEN '2026-01-31 13:00:00' AND '2026-01-31 13:30:00' ORDER BY id DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(!$rows){
    echo "No exact-match rows found for message text in 13:00-13:30 window.\n";
} else {
    echo "Found rows:\n";
    foreach($rows as $r){
        echo "id={$r['id']}, sender_email={$r['sender_email']}, receiver_email={$r['receiver_email']}, created_at={$r['created_at']}, message_id={$r['message_id']}\n";
        echo "subject={$r['subject']}\n";
        echo "message=".substr($r['message'],0,200)."\n----\n";
    }
}

// Broader search by timestamp only (>= 2026-01-31 13:00)
$sql2 = "SELECT id,sender_email,receiver_email,subject,message,created_at,timestamp_normalized,message_id FROM chattbl WHERE created_at >= '2026-01-31 13:00:00' ORDER BY created_at DESC, id DESC LIMIT 200";
$stmt2 = $pdo->query($sql2);
$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
if($rows2){
    echo "\nRows with created_at >= 2026-01-31 13:00:00 (latest first):\n";
    foreach($rows2 as $r){
        echo "id={$r['id']}, created_at={$r['created_at']}, sender_email={$r['sender_email']}, subject={$r['subject']}, message_id={$r['message_id']}\n";
    }
} else {
    echo "\nNo rows with created_at >= 2026-01-31 13:00:00\n";
}

// Check any rows with id >= 2899
$sql3 = "SELECT id,sender_email,receiver_email,created_at,subject,message_id FROM chattbl WHERE id >= 2899 ORDER BY id ASC LIMIT 50";
$stmt3 = $pdo->query($sql3);
$rows3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);

if($rows3){
    echo "\nRows with id >= 2899:\n";
    foreach($rows3 as $r){
        echo "id={$r['id']}, created_at={$r['created_at']}, sender_email={$r['sender_email']}, subject={$r['subject']}, message_id={$r['message_id']}\n";
    }
} else {
    echo "\nNo rows with id >= 2899 found.\n";
}

// Search by sender_email variants
$sql4 = "SELECT id,created_at,subject,message_id FROM chattbl WHERE sender_email LIKE '%jerome%' AND created_at >= '2026-01-31 12:00:00' ORDER BY created_at DESC LIMIT 200";
$stmt4 = $pdo->query($sql4);
$rows4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);
if($rows4){
    echo "\nRows from sender LIKE '%jerome%' since noon:\n";
    foreach($rows4 as $r){
        echo "id={$r['id']}, created_at={$r['created_at']}, subject={$r['subject']}, message_id={$r['message_id']}\n";
    }
} else {
    echo "\nNo rows from sender LIKE '%jerome%' since noon.\n";
}

echo "\nDone.\n";
