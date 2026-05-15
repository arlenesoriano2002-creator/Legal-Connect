<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\TabSessionManager;
use App\Helpers\TabAuthHelper;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function showAdminLoginForm()
    {
        return view('admin');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // ✅ SECURITY: Prevent login if not verified (OTP protection)
            if (!$user->is_verified) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Please verify your account first using OTP.'
                ]);
            }

            // Set user as online
            $user->update(['active_status' => 1]);

            // Track session activity
            $request->session()->put('last_activity_timestamp', time());

            \Log::info('Login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);

            // Logout previous sessions
            try {
                TabSessionManager::logoutAllUserTabs($user->id);
            } catch (\Exception $e) {
                \Log::warning('Failed to logout previous sessions', [
                    'error' => $e->getMessage()
                ]);
            }

            // Role-based guard login
            try {
                $roleGuard = match ($user->role) {
                    'admin', 'superadmin' => 'admin',
                    'diffun_staff' => 'diffun_staff',
                    'cordon_staff' => 'cordon_staff',
                    'client' => 'client',
                    default => 'web',
                };

                if (!Auth::guard($roleGuard)->check()) {
                    Auth::guard($roleGuard)->login($user);
                }
            } catch (\Throwable $e) {
                \Log::warning('Guard login failed', ['error' => $e->getMessage()]);
            }

            // Tab session handling (unchanged)
            $tabId = $request->input('tab_id', '');
            $tabSessionData = null;

            if ($tabId) {
                try {
                    $tabSessionData = TabSessionManager::generateTabToken($user, $tabId);
                    $tabSessionData['user_id'] = $user->id;
                    $tabSessionData['role'] = $user->role;
                } catch (\Exception $e) {
                    $tabSessionData = null;
                }
            }

            if ($tabSessionData) {
                if (!isset($tabSessionData['created_at'])) {
                    $tabSessionData['created_at'] = now()->toIso8601String();
                }
                TabAuthHelper::storeTabSession($request, $tabSessionData);
            } else {
                $fallback = [
                    'tab_token' => 'session_' . \Illuminate\Support\Str::random(32),
                    'tab_id' => $tabId ?: 'tab_' . uniqid(),
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'created_at' => now()->toIso8601String(),
                ];
                TabAuthHelper::storeTabSession($request, $fallback);
                $tabSessionData = $fallback;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect' => $this->getRedirectUrl($user),
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                    'tab_session' => [
                        'tab_token' => $tabSessionData['tab_token'] ?? null,
                        'expires_at' => now()->addHours(24)->toIso8601String(),
                    ]
                ]);
            }

            return redirect($this->getRedirectUrl($user));
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ]);
    }

   private function getRedirectUrl(User $user): string
{
    return match ($user->role) {
        'admin' => route('superadmin.page'),
        'lawyer' => route('admindashboard'),
        'client' => '/welcome',

        // ✅ CLEAN: ONE ROLE ONLY
        'staff' => route('dashboardStaff'),
        'secretary' => route('dashboardStaff'),

        default => '/welcome',
    };
}
    public function showAdminProfile()
    {
        $authUser = Auth::user();

        if (!$authUser) {
            return redirect('/login')->withErrors(['message' => 'You must be logged in.']);
        }

        $user = User::where('email', $authUser->email)->first();

        return view('adminAccount', compact('user'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->update(['active_status' => 0]);
        }

        $tabToken = $request->header('X-Tab-Token') ?? $request->session()->get('tab_session.tab_token');

        if ($tabToken) {
            try {
                TabSessionManager::logoutTab($tabToken);
            } catch (\Exception $e) {}
            TabAuthHelper::clearTabSession($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/welcome');
    }
}
