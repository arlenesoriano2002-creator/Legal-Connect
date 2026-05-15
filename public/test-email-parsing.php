<?php
// Load Laravel's autoloader
require __DIR__.'/../vendor/autoload.php';

// Boot Laravel application (optional but might be needed)
// $app = require_once __DIR__.'/../bootstrap/app.php';
// $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Carbon\Carbon;

// Test different date formats
$testDates = [
    "Fri, 2 Jan 2026 11:22:00 +0000",
    "Fri, 2 Jan 2026 11:22:00 UTC",
    "Fri, 2 Jan 2026 11:22:00 GMT",
    "Fri, 2 Jan 2026 11:22:00",
    "Jan 2, 2026 11:22:00 AM",
    "2026-01-02 11:22:00",
    "02 Jan 2026 11:22:00 +0800",
    "Jan. 2 2026 7:22 pm", // Your problematic date format
];

echo "<!DOCTYPE html>
<html>
<head>
    <title>Email Date Parsing Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #333; }
        hr { border: 1px solid #ddd; }
        .test-case { margin: 10px 0; padding: 10px; background: #f5f5f5; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h2>Email Date Parsing Test</h2>
    <p><strong>Current Manila time:</strong> " . Carbon::now('Asia/Manila')->toDateTimeString() . "</p>";

foreach ($testDates as $testDate) {
    echo "<div class='test-case'>
            <h3>Testing: <code>{$testDate}</code></h3>";
    
    try {
        // Method 1: Carbon parse
        $carbon = Carbon::parse($testDate);
        echo "<p class='info'>Carbon::parse: {$carbon->toDateTimeString()} ({$carbon->getTimezone()->getName()})</p>";
        
        // Method 2: Carbon parse with UTC
        $carbonUTC = Carbon::parse($testDate . (strpos($testDate, '+') === false && strpos($testDate, 'UTC') === false && strpos($testDate, 'GMT') === false ? ' UTC' : ''));
        echo "<p class='info'>Carbon::parse as UTC: {$carbonUTC->toDateTimeString()} ({$carbonUTC->getTimezone()->getName()})</p>";
        
        // Method 3: Convert to Manila
        $manila = $carbonUTC->setTimezone('Asia/Manila');
        echo "<p class='success'>Manila time: {$manila->toDateTimeString()} ({$manila->getTimezone()->getName()})</p>";
        
        // Method 4: strtotime
        $timestamp = strtotime($testDate);
        echo "<p>strtotime timestamp: {$timestamp}</p>";
        echo "<p>strtotime result: " . date('Y-m-d H:i:s', $timestamp) . "</p>";
        
    } catch (Exception $e) {
        echo "<p class='error'>Error: {$e->getMessage()}</p>";
    }
    
    echo "</div><hr>";
}

echo "</body></html>";