<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class MarkGuestUserOffline
{
    public const LAST_USER_COOKIE = 'legal_connect_last_user';

    public function handle(Request $request, Closure $next)
    {
        if ($request->routeIs('login') && Auth::check()) {
            $user = Auth::user();

            if ($user && (int) $user->active_status !== 0) {
                $user->forceFill(['active_status' => 0])->save();
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if (!Auth::check()) {
            $lastUserId = $request->cookie(self::LAST_USER_COOKIE);

            if ($lastUserId) {
                User::whereKey((int) $lastUserId)
                    ->where('active_status', '!=', 0)
                    ->update(['active_status' => 0]);
            }

            $response = $next($request);

            if ($lastUserId) {
                Cookie::queue(Cookie::forget(self::LAST_USER_COOKIE));
            }

            return $response;
        }

        return $next($request);
    }
}
