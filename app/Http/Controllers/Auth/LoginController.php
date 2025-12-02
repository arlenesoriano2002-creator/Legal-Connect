<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AdminLogin;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login'); // your login page
    }

    public function showAdminLoginForm()
    {
        return view('admin'); // admin login page
    }

  public function login(Request $request)
{
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required'
    ]);

    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
        $request->session()->regenerate();
        $user = Auth::user();
        
        // Debug output - check what's happening
        \Log::info('Login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role
        ]);
        
        if ($user->role === 'client') {
            return redirect('/welcome');
        } elseif ($user->role === 'admin') {
            \Log::info('Redirecting admin to dashboard');
            return redirect()->route('admindashboard');
        } elseif ($user->role === 'staff') {
            return redirect()->route('dashboardStaff');
        } elseif ($user->role === 'superadmin') {
            return redirect()->route('superadmin.page');
        } else {
            return redirect('/welcome');
        }
    }

    \Log::warning('Login failed for email: ' . $request->email);
    return back()->withErrors([
        'email' => 'Invalid credentials.',
    ]);
}



    public function showAdminProfile()
    {
        $authUser = Auth::user();

        if (!$authUser) {
            return redirect('/login')->withErrors(['message' => 'You must be logged in.']);
        }

        // Search using email (since email is unique for each user)
        $user = User::where('email', $authUser->email)->first();

        return view('adminAccount', compact('user'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/welcome');
    }


}
