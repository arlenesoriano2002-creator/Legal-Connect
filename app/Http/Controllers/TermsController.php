<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TermsController extends Controller
{
    public function show()
    {
        Log::info('TermsController: show method called');
        Log::info('Current session status_approval: ' . session('status_approval', 'NOT SET'));
        
        return view('Terms');
    }

   public function accept(Request $request)
{
    Log::info('TermsController: accept method called');
    Log::info('Request data:', $request->all());
    Log::info('Session before setting status_approval:', session()->all());

    // Validate the checkbox was checked
    $request->validate([
        'acceptTerms' => 'required|accepted'
    ], [
        'acceptTerms.required' => 'You must accept the terms and conditions to continue.',
        'acceptTerms.accepted' => 'You must accept the terms and conditions to continue.'
    ]);

    // Store terms acceptance in session
    session([
        'status_approval' => 'approved',
        'terms_accepted_at' => now()
    ]);
    
    // Save the session to ensure persistence
    $request->session()->save();
    
    Log::info('Session after setting status_approval: ' . session('status_approval'));
    Log::info('Full session after setting:', session()->all());
    
    // Redirect to appointment1 (next step in flow)
    return redirect()->route('appointment1')->with('success', 'Terms accepted successfully.');
}
}