<?php
/**
 * Compare staff calendar vs lawyer calendar endpoints
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\CalendarController;

echo "=============================================================\n";
echo "STAFF VS LAWYER CALENDAR COMPARISON\n";
echo "=============================================================\n\n";

// Get a staff user
$staffUser = DB::table('users')
    ->where('role', 'staff')
    ->whereNotNull('law_office_id')
    ->first();

// Get a lawyer from the same office
$lawyerUser = DB::table('users')
    ->where('role', 'lawyer')
    ->where('law_office_id', $staffUser->law_office_id)
    ->first();

if (!$staffUser || !$lawyerUser) {
    echo "ERROR: Could not find staff and lawyer from same office.\n";
    
    echo "\nStaff user found: " . ($staffUser ? "YES" : "NO");
    echo "\nLawyer user found: " . ($lawyerUser ? "YES" : "NO");
    
    if ($staffUser) {
        echo "\nLawyer from office " . $staffUser->law_office_id . ": ";
        $count = DB::table('users')
            ->where('role', 'lawyer')
            ->where('law_office_id', $staffUser->law_office_id)
            ->count();
        echo $count;
    }
    exit(1);
}

$month = '2026-04';

echo "Comparing calendars for office: " . $staffUser->law_office_id . " (April 2026)\n";
echo "---\n\n";

// Test STAFF calendar
echo "STAFF Calendar (/staff/calendar/month/colors)\n";
echo "User: " . $staffUser->name . " (ID: " . $staffUser->id . ", Role: " . $staffUser->role . ")\n";

Auth::loginUsingId($staffUser->id);

try {
    $request = new Request();
    $request->merge(['month' => $month]);
    
    $controller = new StaffController();
    $response = $controller->getMonthColors($request);
    $staffData = json_decode($response->getContent(), true);
    
    echo "Status: " . $staffData['status'] . "\n";
    echo "Records: " . count($staffData['data']) . "\n";
    
    $staffDates = array_keys($staffData['data']);
    sort($staffDates);
    
    echo "Dates: " . implode(", ", $staffDates) . "\n\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
    $staffData = ['data' => []];
}

// Test LAWYER calendar
echo "LAWYER Calendar (/calendar/month/colors)\n";
echo "User: " . $lawyerUser->name . " (ID: " . $lawyerUser->id . ", Role: " . $lawyerUser->role . ")\n";

Auth::loginUsingId($lawyerUser->id);

try {
    $request = new Request();
    $request->merge(['month' => $month]);
    
    $controller = new CalendarController();
    $response = $controller->getMonthColors($request);
    $lawyerData = json_decode($response->getContent(), true);
    
    echo "Status: " . $lawyerData['status'] . "\n";
    echo "Records: " . count($lawyerData['data']) . "\n";
    
    $lawyerDates = array_keys($lawyerData['data']);
    sort($lawyerDates);
    
    echo "Dates: " . implode(", ", $lawyerDates) . "\n\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
    $lawyerData = ['data' => []];
}

// Compare
echo "=============================================================\n";
echo "COMPARISON\n";
echo "=============================================================\n\n";

$staffDates = isset($staffData['data']) ? array_keys($staffData['data']) : [];
$lawyerDates = isset($lawyerData['data']) ? array_keys($lawyerData['data']) : [];

sort($staffDates);
sort($lawyerDates);

$same = $staffDates === $lawyerDates;

echo "Staff records: " . count($staffDates) . "\n";
echo "Lawyer records: " . count($lawyerDates) . "\n";
echo "Match: " . ($same ? "✓ YES" : "✗ NO") . "\n\n";

if (!$same) {
    $staffOnly = array_diff($staffDates, $lawyerDates);
    $lawyerOnly = array_diff($lawyerDates, $staffDates);
    
    if (!empty($staffOnly)) {
        echo "Staff only has: " . implode(", ", $staffOnly) . "\n";
    }
    if (!empty($lawyerOnly)) {
        echo "Lawyer only has: " . implode(", ", $lawyerOnly) . "\n";
    }
}
