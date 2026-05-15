<?php

/**
 * Per-Tab Session Diagnostic Tool
 * 
 * Run this after logging in to verify:
 * 1. Session storage is working
 * 2. Database records are created
 * 3. PerTabAuthHelper can retrieve correct users
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Get authenticated user
$auth = \Illuminate\Support\Facades\Auth::user();
$session = \Illuminate\Support\Facades\Session::all();

echo "\n=== PER-TAB SESSION DIAGNOSTIC ===\n\n";

echo "1. GLOBAL AUTHENTICATION:\n";
if ($auth) {
    echo "   ✓ Auth::user() found: " . $auth->email . " (ID: {$auth->id})\n";
} else {
    echo "   ✗ Auth::user() is NULL - User not logged in globally\n";
}

echo "\n2. SESSION DATA:\n";
if (isset($session['tab_session'])) {
    $tabSession = $session['tab_session'];
    echo "   ✓ session['tab_session'] found:\n";
    echo "      - user_id: " . ($tabSession['user_id'] ?? 'MISSING') . "\n";
    echo "      - tab_token: " . (isset($tabSession['tab_token']) ? substr($tabSession['tab_token'], 0, 10) . '...' : 'MISSING') . "\n";
    echo "      - tab_id: " . ($tabSession['tab_id'] ?? 'MISSING') . "\n";
    echo "      - role: " . ($tabSession['role'] ?? 'MISSING') . "\n";
} else {
    echo "   ✗ session['tab_session'] NOT found\n";
}

echo "\n3. PER-TAB AUTH HELPER:\n";
try {
    $tabUser = \App\Helpers\PerTabAuthHelper::getTabUser();
    if ($tabUser) {
        echo "   ✓ PerTabAuthHelper::getTabUser() returned: " . $tabUser->email . " (ID: {$tabUser->id})\n";
    } else {
        echo "   ✗ PerTabAuthHelper::getTabUser() returned NULL\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Error calling PerTabAuthHelper::getTabUser(): " . $e->getMessage() . "\n";
}

echo "\n4. DATABASE TAB_SESSIONS TABLE:\n";
try {
    $count = \Illuminate\Support\Facades\DB::table('tab_sessions')->count();
    echo "   ✓ tab_sessions table exists with $count records\n";
    
    // Show recent records
    $recent = \Illuminate\Support\Facades\DB::table('tab_sessions')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    if ($recent->count() > 0) {
        echo "   Recent entries:\n";
        foreach ($recent as $record) {
            echo "   - User ID {$record->user_id}: " . substr($record->tab_token, 0, 15) . "... (expires: {$record->expires_at})\n";
        }
    }
} catch (\Exception $e) {
    echo "   ✗ Error reading tab_sessions table: " . $e->getMessage() . "\n";
}

echo "\n5. REQUEST HEADERS:\n";
$tabTokenHeader = $request->header('X-Tab-Token');
if ($tabTokenHeader) {
    echo "   ✓ X-Tab-Token header found: " . substr($tabTokenHeader, 0, 15) . "...\n";
} else {
    echo "   ✗ X-Tab-Token header NOT found\n";
    echo "   (This is normal if accessed directly - headers are only sent by JavaScript)\n";
}

echo "\n6. SUMMARY:\n";
if ($auth && isset($session['tab_session']) && isset($session['tab_session']['user_id'])) {
    echo "   ✓ Per-tab session appears to be working!\n";
} else {
    echo "   ✗ Per-tab session may not be set up correctly\n";
}

echo "\n=== END DIAGNOSTIC ===\n\n";
