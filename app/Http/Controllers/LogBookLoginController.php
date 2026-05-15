<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogBookLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LogBookLoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('logbook.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by username
        $user = LogBookLogin::where('username', $request->username)->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'username' => 'Invalid credentials.',
            ])->withInput();
        }

        // Store user in session
        session(['logbook_user' => $user]);

        // Redirect based on branch
        if ($user->branch === 'diffun') {
            return redirect('/walkin-logbook/diffun');
        } else {
            return redirect('/walkin-logbook/cordon');
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        // Set user as offline before logout
        $user = Auth::user();
        if ($user) {
            $user->update(['active_status' => 0]);
        }
        
        // Forget session-based logbook user
        session()->forget('logbook_user');
        
        // Also logout from regular Auth guard if user is authenticated
        if (Auth::check()) {
            Auth::logout();
        }
        
        // Invalidate and regenerate session to prevent CSRF issues
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/welcome');
    }

    public function showDiffunLogbook()
    {
        // Allow public access to the Diffun logbook page.
        // If a logbook session exists, enforce branch matching only for branch-specific access.
        $logbookUser = session('logbook_user');

        if ($logbookUser && $logbookUser->branch !== 'diffun') {
            abort(403, 'Unauthorized for this branch.');
        }

        return view('walkin logbook.diffun_logbook.index');
    }

    public function showCordonLogbook()
    {
        if (!session('logbook_user')) {
            return redirect('/walkin-logbook/login');
        }

        if (session('logbook_user')->branch !== 'cordon') {
            abort(403, 'Unauthorized for this branch.');
        }

        return view('walkin logbook.cordon_logbook.index');
    }

    /**
     * Create a sample user (for testing)
     */
    public function createSampleUser()
    {
        // Create diffun user
        LogBookLogin::create([
            'username' => 'diffun_admin',
            'password' => 'password123',
            'branch' => 'diffun'
        ]);

        // Create cordon user
        LogBookLogin::create([
            'username' => 'cordon_admin',
            'password' => 'password123',
            'branch' => 'cordon'
        ]);

        return "Sample users created!";
    }
}