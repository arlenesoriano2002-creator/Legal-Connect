<?php
/**
 * Migration script to assign legacy calendar data to a specific law office
 * Usage: php migrate_legacy_calendar_data.php <office_id>
 * Example: php migrate_legacy_calendar_data.php 1
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=============================================================\n";
echo "LEGACY CALENDAR DATA MIGRATION\n";
echo "=============================================================\n\n";

// Get office_id from command line argument
$officeId = $argv[1] ?? null;

if (!$officeId) {
    echo "Usage: php migrate_legacy_calendar_data.php <office_id>\n";
    echo "Example: php migrate_legacy_calendar_data.php 1\n\n";
    
    echo "Available law offices:\n";
    $offices = DB::table('law_offices')->select('id', 'law_office')->get();
    foreach ($offices as $office) {
        echo "  ID: " . $office->id . " | " . $office->law_office . "\n";
    }
    echo "\n";
    exit(1);
}

// Validate office exists
$office = DB::table('law_offices')->where('id', $officeId)->first();
if (!$office) {
    echo "ERROR: Law office with ID $officeId not found.\n\n";
    exit(1);
}

echo "Migrating legacy calendar data to: $office->law_office (ID: $officeId)\n\n";

// Check current legacy data
$monthCount = DB::table('month_colors')->whereNull('law_office_id')->count();
$weekCount = DB::table('week_colors')->whereNull('law_office_id')->count();

echo "Before migration:\n";
echo "  month_colors with NULL law_office_id: $monthCount\n";
echo "  week_colors with NULL law_office_id: $weekCount\n\n";

if ($monthCount == 0 && $weekCount == 0) {
    echo "No legacy data found. Nothing to migrate.\n";
    exit(0);
}

// Perform migration
echo "Starting migration...\n";

try {
    DB::beginTransaction();
    
    // Migrate month_colors
    if ($monthCount > 0) {
        $updated = DB::table('month_colors')
            ->whereNull('law_office_id')
            ->update(['law_office_id' => $officeId]);
        
        echo "✓ Updated $updated month_colors records\n";
    }
    
    // Migrate week_colors
    if ($weekCount > 0) {
        $updated = DB::table('week_colors')
            ->whereNull('law_office_id')
            ->update(['law_office_id' => $officeId]);
        
        echo "✓ Updated $updated week_colors records\n";
    }
    
    DB::commit();
    
    echo "\n✓ Migration completed successfully!\n\n";
    
    // Verify
    $monthNull = DB::table('month_colors')->whereNull('law_office_id')->count();
    $weekNull = DB::table('week_colors')->whereNull('law_office_id')->count();
    
    echo "After migration:\n";
    echo "  month_colors with NULL law_office_id: $monthNull\n";
    echo "  week_colors with NULL law_office_id: $weekNull\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: Migration failed!\n";
    echo "Reason: " . $e->getMessage() . "\n";
    exit(1);
}
