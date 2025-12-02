<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckTermsAccepted
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('CheckTermsAccepted middleware - status_approval: ' . session('status_approval', 'NOT SET'));
        
        if (session('status_approval') !== 'approved') {
            Log::warning('Terms not accepted, redirecting to Terms page');
            return redirect()->route('Terms')->with('error', 'Please accept the terms and conditions first.');
        }

        return $next($request);
    }
}