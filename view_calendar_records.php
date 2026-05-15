<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Get staff and lawyer from same office
$staff = DB::table('users')->where('role', 'staff')->whereNotNull('law_office_id')->first();
$lawyer = DB::table('users')->where('role', 'lawyer')->where('law_office_id', $staff->law_office_id)->first();

echo "=============================================================\n";
echo "DATABASE RECORDS COMPARISON\n";
echo "=============================================================\n\n";

echo "Staff: " . $staff->name . " (Office: " . $staff->law_office_id . ")\n";
echo "Lawyer: " . $lawyer->name . " (Office: " . $lawyer->law_office_id . ")\n\n";

echo "month_colors table for April 2026:\n";
echo "---\n";

$allRecords = DB::table('month_colors')
    ->whereBetween('date', ['2026-04-01', '2026-04-30'])
    ->orderBy('date')
    ->get();

echo "All records (including NULL law_office_id):\n";
foreach ($allRecords as $row) {
    $officeId = $row->law_office_id ?? 'NULL';
    echo "  " . $row->date . " | office_id: " . $officeId . " | color: " . $row->date_color . "\n";
}

echo "\n";
echo "week_colors table for April 2026 (sample):\n";
echo "---\n";

$weekSample = DB::table('week_colors')
    ->whereBetween('date', ['2026-04-18', '2026-04-21'])
    ->orderBy('date')
    ->orderBy('time_slot')
    ->get();

$currentDate = null;
foreach ($weekSample as $row) {
    if ($currentDate !== $row->date) {
        echo "\n" . $row->date . " | office_id: " . ($row->law_office_id ?? 'NULL') . "\n";
        $currentDate = $row->date;
    }
    echo "  Slot " . $row->time_slot . ": " . $row->time . " (color: " . $row->color . ", slots: " . $row->slot_number . ")\n";
}
