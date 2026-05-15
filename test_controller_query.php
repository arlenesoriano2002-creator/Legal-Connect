<?php
/**
 * Test Controller Query Diagnostic
 * Simulates exactly what the MessageInquiriesController does
 */

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;

try {
    // Use the same query the controller uses
    echo "=== TESTING CONTROLLER QUERY ===\n\n";
    
    $inquiries = DB::table('concerns_inquiries_message')
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "Query executed successfully.\n";
    echo "Total records returned: " . $inquiries->count() . "\n\n";
    
    if ($inquiries->count() > 0) {
        echo "=== FIRST RECORD DETAILS ===\n";
        $first = $inquiries->first();
        
        echo "Object type: " . get_class($first) . "\n";
        echo "Object properties (cast to array):\n";
        
        $arrayVersion = (array) $first;
        foreach ($arrayVersion as $key => $value) {
            echo "  - $key: " . (is_null($value) ? 'NULL' : (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value)) . "\n";
        }
        
        echo "\n=== CHECKING SUBJECT PROPERTY ACCESS ===\n";
        echo "Access via \$first->subject: ";
        if (isset($first->subject)) {
            echo "EXISTS - Value: '" . $first->subject . "'\n";
        } else {
            echo "DOES NOT EXIST\n";
        }
        
        echo "Access via property_exists(): " . (property_exists($first, 'subject') ? 'YES' : 'NO') . "\n";
        
        echo "\n=== FIRST 3 RECORDS SUBJECT VALUES ===\n";
        foreach ($inquiries->take(3) as $i => $inquiry) {
            echo "Record " . ($i + 1) . ": subject = '" . ($inquiry->subject ?? 'NULL') . "'\n";
        }
        
        echo "\n=== TESTING BLADE CONDITION ===\n";
        $testSubject = $inquiries->first()->subject;
        echo "Test value: '$testSubject'\n";
        echo "!empty(\$testSubject): " . (!empty($testSubject) ? 'TRUE' : 'FALSE') . "\n";
        echo "is_null(\$testSubject): " . (is_null($testSubject) ? 'TRUE' : 'FALSE') . "\n";
        echo "strlen(\$testSubject): " . strlen($testSubject) . "\n";
    }
    
    echo "\n=== QUERY DIAGNOSTIC COMPLETE ===\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
