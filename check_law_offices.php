<?php
/**
 * Script to view law offices and help with legacy data migration
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=============================================================\n";
echo "LAW OFFICES & LEGACY DATA MIGRATION HELPER\n";
echo "=============================================================\n\n";

// Get all law offices
$offices = DB::table('law_offices')->select('id', 'law_office')->get();

echo "Available Law Offices:\n";
echo "---------------------\n";
if ($offices->isEmpty()) {
    echo "No law offices found.\n";
} else {
    foreach ($offices as $office) {
        echo "ID: " . $office->id . " | Office: " . $office->law_office . "\n";
    }
}
echo "\n";

// Check legacy data
$nullMonthCount = DB::table('month_colors')->whereNull('law_office_id')->count();
$nullWeekCount = DB::table('week_colors')->whereNull('law_office_id')->count();

echo "Legacy Data Summary:\n";
echo "-------------------\n";
echo "month_colors with NULL law_office_id: $nullMonthCount\n";
echo "week_colors with NULL law_office_id: $nullWeekCount\n\n";

if ($nullMonthCount > 0 || $nullWeekCount > 0) {
    echo "To assign all legacy data to office ID 1, run:\n\n";
    echo "  UPDATE month_colors SET law_office_id = 1 WHERE law_office_id IS NULL;\n";
    echo "  UPDATE week_colors SET law_office_id = 1 WHERE law_office_id IS NULL;\n\n";
    
    echo "Or use this PHP command:\n";
    echo "  php migrate_legacy_calendar_data.php 1\n\n";
    
    echo "Options:\n";
    echo "  - Replace '1' with the appropriate law_office_id\n";
    echo "  - Or delete the legacy data if no longer needed:\n";
    echo "    DELETE FROM month_colors WHERE law_office_id IS NULL;\n";
    echo "    DELETE FROM week_colors WHERE law_office_id IS NULL;\n";
}
