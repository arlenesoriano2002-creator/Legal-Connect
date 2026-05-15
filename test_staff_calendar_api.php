<?php
/**
 * Test the updated staff calendar endpoints
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\StaffController;

echo "=============================================================\n";
echo "STAFF CALENDAR ENDPOINTS TEST\n";
echo "=============================================================\n\n";

// Get a staff/secretary user for testing
$user = DB::table('users')
    ->whereIn('role', ['secretary', 'staff'])
    ->whereNotNull('law_office_id')
    ->first();

if (!$user) {
    echo "ERROR: No staff/secretary user with law_office_id found.\n";
    exit(1);
}

echo "Testing with user:\n";
echo "  ID: " . $user->id . "\n";
echo "  Name: " . $user->name . "\n";
echo "  Role: " . $user->role . "\n";
echo "  Law Office ID: " . $user->law_office_id . "\n\n";

// Simulate authentication
Auth::loginUsingId($user->id);

// Create a request for getMonthColors
echo "TEST 1: GET /staff/calendar/month/colors\n";
echo "---\n";

try {
    $request = new Request();
    $request->merge(['month' => '2026-04']);
    
    $controller = new StaffController();
    $response = $controller->getMonthColors($request);
    $data = json_decode($response->getContent(), true);
    
    echo "Status: " . $data['status'] . "\n";
    echo "Records found: " . count($data['data']) . "\n";
    echo "Sample data:\n";
    
    $count = 0;
    foreach ($data['data'] as $date => $info) {
        echo "  - $date: color=" . $info['color'] . "\n";
        if (++$count >= 3) break;
    }
    echo "\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}

// Test getDateData
echo "TEST 2: GET /staff/calendar/date-data\n";
echo "---\n";

try {
    $testDate = '2026-04-20';
    $request = new Request();
    $request->merge(['date' => $testDate]);
    
    $controller = new StaffController();
    $response = $controller->getDateData($request);
    $data = json_decode($response->getContent(), true);
    
    echo "Date: " . $testDate . "\n";
    echo "Status: " . $data['status'] . "\n";
    echo "Date Color: " . ($data['data']['date_color'] ?? 'null') . "\n";
    echo "Time Slots: " . count($data['data']['time_slots']) . "\n\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}

// Check database directly
echo "TEST 3: Database verification\n";
echo "---\n";

$monthColors = DB::table('month_colors')
    ->where('month', '2026-04')
    ->where('law_office_id', $user->law_office_id)
    ->get();

$weekColors = DB::table('week_colors')
    ->whereBetween('date', ['2026-04-01', '2026-04-30'])
    ->where('law_office_id', $user->law_office_id)
    ->get();

echo "month_colors records for office " . $user->law_office_id . " in Apr 2026: " . $monthColors->count() . "\n";
echo "week_colors records for office " . $user->law_office_id . " in Apr 2026: " . $weekColors->count() . "\n\n";

echo "=============================================================\n";
echo "TEST COMPLETE\n";
echo "=============================================================\n";
