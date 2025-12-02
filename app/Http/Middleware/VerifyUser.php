<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifiedUser // Change this to match filename, or vice versa
{
   public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->is_verified) {
            return redirect()->route('otp.form')->with('error', 'Please verify your account first.');
        }

        return $next($request);
    }
}