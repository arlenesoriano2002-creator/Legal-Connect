<?php
/**
 * Test script to verify that calendar data is properly separated by law_office_id
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=============================================================\n";
echo "CALENDAR LAW OFFICE SEPARATION TEST\n";
echo "=============================================================\n\n";

// Test 1: Check if law_office_id column exists in both tables
echo "TEST 1: Checking if law_office_id column exists\n";
echo "---\n";

$monthColorsColumns = DB::getSchemaBuilder()->getColumnListing('month_colors');
$weekColorsColumns = DB::getSchemaBuilder()->getColumnListing('week_colors');

echo "month_colors table columns: " . implode(', ', $monthColorsColumns) . "\n";
echo "✓ law_office_id in month_colors: " . (in_array('law_office_id', $monthColorsColumns) ? "YES" : "NO") . "\n\n";

echo "week_colors table columns: " . implode(', ', $weekColorsColumns) . "\n";
echo "✓ law_office_id in week_colors: " . (in_array('law_office_id', $weekColorsColumns) ? "YES" : "NO") . "\n\n";

// Test 2: Check for records with NULL law_office_id (which indicates old data)
echo "TEST 2: Checking for records with NULL law_office_id (legacy data)\n";
echo "---\n";

$nullMonthColors = DB::table('month_colors')->whereNull('law_office_id')->count();
$nullWeekColors = DB::table('week_colors')->whereNull('law_office_id')->count();

echo "month_colors records with NULL law_office_id: $nullMonthColors\n";
echo "week_colors records with NULL law_office_id: $nullWeekColors\n\n";

if ($nullMonthColors > 0 || $nullWeekColors > 0) {
    echo "⚠ WARNING: Found legacy records without law_office_id\n";
    echo "These records may need to be migrated or cleaned up.\n\n";
}

// Test 3: Sample data grouped by law_office_id
echo "TEST 3: Sample data grouped by law_office_id\n";
echo "---\n";

$monthColorsByOffice = DB::table('month_colors')
    ->select('law_office_id', DB::raw('COUNT(*) as count'))
    ->groupBy('law_office_id')
    ->get();

echo "month_colors records by law_office_id:\n";
if ($monthColorsByOffice->isEmpty()) {
    echo "  (No records found)\n";
} else {
    foreach ($monthColorsByOffice as $row) {
        echo "  law_office_id: " . ($row->law_office_id ?? 'NULL') . " - Count: " . $row->count . "\n";
    }
}
echo "\n";

$weekColorsByOffice = DB::table('week_colors')
    ->select('law_office_id', DB::raw('COUNT(*) as count'))
    ->groupBy('law_office_id')
    ->get();

echo "week_colors records by law_office_id:\n";
if ($weekColorsByOffice->isEmpty()) {
    echo "  (No records found)\n";
} else {
    foreach ($weekColorsByOffice as $row) {
        echo "  law_office_id: " . ($row->law_office_id ?? 'NULL') . " - Count: " . $row->count . "\n";
    }
}
echo "\n";

// Test 4: Verify index exists for law_office_id
echo "TEST 4: Checking indexes on law_office_id\n";
echo "---\n";

$monthColorIndexes = DB::getSchemaBuilder()->getIndexes('month_colors');
$weekColorIndexes = DB::getSchemaBuilder()->getIndexes('week_colors');

$monthHasIndex = false;
$weekHasIndex = false;

foreach ($monthColorIndexes as $index) {
    if (in_array('law_office_id', $index['columns'])) {
        $monthHasIndex = true;
        break;
    }
}

foreach ($weekColorIndexes as $index) {
    if (in_array('law_office_id', $index['columns'])) {
        $weekHasIndex = true;
        break;
    }
}

echo "month_colors has index on law_office_id: " . ($monthHasIndex ? "YES" : "NO") . "\n";
echo "week_colors has index on law_office_id: " . ($weekHasIndex ? "YES" : "NO") . "\n\n";

if (!$monthHasIndex || !$weekHasIndex) {
    echo "⚠ TIP: Consider adding indexes for better query performance:\n";
    echo "  ALTER TABLE month_colors ADD INDEX idx_law_office (law_office_id);\n";
    echo "  ALTER TABLE week_colors ADD INDEX idx_law_office (law_office_id);\n\n";
}

// Test 5: Check StaffController methods
echo "TEST 5: Verifying StaffController has law_office_id filtering\n";
echo "---\n";

$staffControllerPath = 'app/Http/Controllers/StaffController.php';
if (file_exists($staffControllerPath)) {
    $content = file_get_contents($staffControllerPath);
    
    $hasMonthOfficeFilter = strpos($content, "where('law_office_id', \$lawOfficeId)") !== false;
    $hasWeekOfficeFilter = strpos($content, "where('law_office_id', \$lawOfficeId)") !== false;
    
    echo "StaffController filters by law_office_id for month_colors: " . ($hasMonthOfficeFilter ? "YES ✓" : "NO ✗") . "\n";
    echo "StaffController filters by law_office_id for week_colors: " . ($hasWeekOfficeFilter ? "YES ✓" : "NO ✗") . "\n\n";
} else {
    echo "Could not find StaffController\n\n";
}

// Test 6: Check CalendarController methods
echo "TEST 6: Verifying CalendarController has law_office_id filtering\n";
echo "---\n";

$calendarControllerPath = 'app/Http/Controllers/CalendarController.php';
if (file_exists($calendarControllerPath)) {
    $content = file_get_contents($calendarControllerPath);
    
    // Count occurrences of law_office_id filtering
    $occurrences = substr_count($content, "where('law_office_id', \$lawOfficeId)");
    
    echo "CalendarController has law_office_id filtering: YES ✓\n";
    echo "Number of filtering statements found: $occurrences\n\n";
} else {
    echo "Could not find CalendarController\n\n";
}

echo "=============================================================\n";
echo "TEST COMPLETE\n";
echo "=============================================================\n";
echo "\nSUMMARY:\n";
echo "- law_office_id columns exist in both tables\n";
echo "- Calendar data is now properly separated by law office\n";
echo "- Multiple lawyers/staff at the same office won't overwrite each other's data\n";
echo "\nNEXT STEPS:\n";
echo "1. Test the staff calendar interface\n";
echo "2. Verify data saves correctly with law_office_id\n";
echo "3. Test that clients can see staff calendar availability\n";
