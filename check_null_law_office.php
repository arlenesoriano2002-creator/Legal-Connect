<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking for NULL law_office_id records...\n\n";

$nullMonth = DB::table('month_colors')
    ->whereNull('law_office_id')
    ->whereBetween('date', ['2026-04-01', '2026-04-30'])
    ->get();

$nullWeek = DB::table('week_colors')
    ->whereNull('law_office_id')
    ->whereBetween('date', ['2026-04-01', '2026-04-30'])
    ->get();

echo "April 2026 NULL law_office_id records:\n";
echo "  month_colors: " . $nullMonth->count() . "\n";
echo "  week_colors: " . $nullWeek->count() . "\n\n";

if ($nullMonth->count() > 0) {
    echo "Dates in month_colors with NULL law_office_id:\n";
    foreach ($nullMonth as $row) {
        echo "  - " . $row->date . " (color: " . $row->date_color . ")\n";
    }
}
