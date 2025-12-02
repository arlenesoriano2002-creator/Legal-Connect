<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyUser
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->is_verified) {
            return redirect()->route('otp.form')->with('error', 'Please verify your account first.');
        }

        return $next($request);
    }
}
