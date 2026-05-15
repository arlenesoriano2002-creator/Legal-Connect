<?php
/**
 * Diagnostic Script: Test Per-Tab Session Storage
 * 
 * Run this after login to verify per-tab session data is stored correctly
 * Access: http://localhost/test_per_tab_session.php
 */

// Start Laravel application
require __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

try {
    // Get session data
    $sessionId = session_id();
    
    echo "<h2>Per-Tab Session Diagnostic Report</h2>";
    echo "<p>Session ID: <code>" . $sessionId . "</code></p>";
    
    // Check if user is authenticated
    if (Auth::check()) {
        $globalUser = Auth::user();
        echo "<h3>Global Auth User (Auth::user())</h3>";
        echo "<pre>";
        echo "User ID: " . $globalUser->id . "\n";
        echo "Name: " . $globalUser->name . "\n";
        echo "Email: " . $globalUser->email . "\n";
        echo "Role: " . $globalUser->role . "\n";
        echo "</pre>";
    } else {
        echo "<h3>Not authenticated</h3>";
        echo "<p style='color:red'>You must be logged in to test this.</p>";
        echo "<a href='/login'>Go to login</a>";
        exit;
    }
    
    // Check tab session data
    $tabSession = Session::get('tab_session');
    echo "<h3>Per-Tab Session Data (Session::get('tab_session'))</h3>";
    if ($tabSession) {
        echo "<pre>";
        echo json_encode($tabSession, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo "</pre>";
        
        // Verify user_id exists
        if (isset($tabSession['user_id'])) {
            echo "<p style='color:green'>✓ user_id IS stored in tab_session: " . $tabSession['user_id'] . "</p>";
        } else {
            echo "<p style='color:red'>✗ user_id NOT found in tab_session</p>";
        }
        
        // Verify role exists
        if (isset($tabSession['role'])) {
            echo "<p style='color:green'>✓ role IS stored in tab_session: " . $tabSession['role'] . "</p>";
        } else {
            echo "<p style='color:red'>✗ role NOT found in tab_session</p>";
        }
    } else {
        echo "<p style='color:red'>✗ tab_session data NOT found in session</p>";
        echo "<p>This means TabAuthHelper::storeTabSession() was not called or failed.</p>";
    }
    
    // Test PerTabAuthHelper
    echo "<h3>Testing PerTabAuthHelper::getTabUser()</h3>";
    try {
        $app = require __DIR__ . '/bootstrap/app.php';
        $app->make('Illuminate\Contracts\Http\Kernel')->handle(
            $request = \Illuminate\Http\Request::capture()
        );
        
        $helper = new \App\Helpers\PerTabAuthHelper();
        $tabUser = $helper->getTabUser();
        
        if ($tabUser) {
            echo "<pre>";
            echo "User ID: " . $tabUser->id . "\n";
            echo "Name: " . $tabUser->name . "\n";
            echo "Email: " . $tabUser->email . "\n";
            echo "Role: " . $tabUser->role . "\n";
            echo "</pre>";
            
            if ($tabUser->id === $globalUser->id) {
                echo "<p style='color:green'>✓ PerTabAuthHelper returns correct user</p>";
            } else {
                echo "<p style='color:orange'>⚠ PerTabAuthHelper returns DIFFERENT user!</p>";
                echo "<p>Global user ID: " . $globalUser->id . "</p>";
                echo "<p>Tab user ID: " . $tabUser->id . "</p>";
            }
        } else {
            echo "<p style='color:red'>✗ PerTabAuthHelper::getTabUser() returned null</p>";
        }
    } catch (\Exception $e) {
        echo "<p style='color:red'>Error testing PerTabAuthHelper: " . $e->getMessage() . "</p>";
    }
    
    // Check all session data
    echo "<h3>Full Session Data</h3>";
    $allSession = Session::all();
    echo "<pre>";
    echo json_encode($allSession, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo "</pre>";
    
    echo "<h3>Recommendations</h3>";
    if (!isset($tabSession['user_id'])) {
        echo "<ol>";
        echo "<li><strong>Check LoginController.php</strong> - Verify user_id is added to tabSessionData before storeTabSession() is called</li>";
        echo "<li><strong>Check TabAuthHelper.php</strong> - storeTabSession() should store entire array with user_id</li>";
        echo "<li><strong>Enable logging</strong> - Check storage/logs/laravel.log for any errors</li>";
        echo "</ol>";
    }
    
} catch (\Exception $e) {
    echo "<h2 style='color:red'>Error</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
