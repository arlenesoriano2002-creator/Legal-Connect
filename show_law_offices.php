<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    $request = \Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

echo "=== LAW OFFICES IN DATABASE ===\n\n";

$offices = DB::table('law_offices')->get();

if ($offices->isEmpty()) {
    echo "No law offices found.\n";
} else {
    echo "Total: " . $offices->count() . " law offices\n\n";
    
    foreach ($offices as $office) {
        echo "ID: {$office->id}\n";
        echo "Law Office: {$office->law_office}\n";
        echo "Lawyer: {$office->lawyer}\n";
        echo "Address: {$office->address}\n";
        if (isset($office->timezone)) echo "Timezone: {$office->timezone}\n";
        if (isset($office->capacity)) echo "Capacity: {$office->capacity}\n";
        echo "Created: {$office->created_at}\n";
        echo "---\n";
    }
}

echo "\n=== USERS ASSOCIATED WITH LAW OFFICES ===\n\n";

$staffByOffice = DB::table('users')
    ->whereNotNull('law_office_id')
    ->select('law_office_id', 'name', 'email', 'role')
    ->orderBy('law_office_id')
    ->get();

if ($staffByOffice->isEmpty()) {
    echo "No staff members assigned to law offices.\n";
} else {
    $currentOfficeId = null;
    foreach ($staffByOffice as $user) {
        if ($currentOfficeId !== $user->law_office_id) {
            $currentOfficeId = $user->law_office_id;
            $officeName = DB::table('law_offices')->where('id', $currentOfficeId)->value('law_office');
            echo "\n--- Office ID {$currentOfficeId} ({$officeName}) ---\n";
        }
        echo "  • {$user->name} ({$user->role}) - {$user->email}\n";
    }
}

echo "\n";
