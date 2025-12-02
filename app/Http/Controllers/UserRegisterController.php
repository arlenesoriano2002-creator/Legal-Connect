<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class UserRegisterController extends Controller
{
    public function showForm()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'country_code'  => 'required|string',
            'phone_number'  => 'required|digits_between:9,15',
            'email'         => 'required|string|email|max:255|unique:users,email',
            'password'      => 'required|string|min:6|confirmed',
        ]);

        // Combine country code and phone number for cp_number field
        $cp_number = $request->country_code . $request->phone_number;

        // Generate OTP
        $otp = rand(1000, 9999);

        // Store data in session temporarily
        session([
            'pending_user' => [
                'name'          => $request->name,
                'cp_number'     => $cp_number, // Store combined number
                'country_code'  => $request->country_code,
                'phone_number'  => $request->phone_number,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'otp'           => $otp,
            ]
        ]);

        // Send OTP email
        try {
            Mail::raw("Your OTP code is: {$otp}", function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Your OTP Code');
            });
            
            // Log successful OTP sending
            Log::info('OTP sent successfully', ['email' => $request->email, 'otp' => $otp]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email', ['email' => $request->email, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to send OTP. Please try again.');
        }

        return redirect()->route('otp.form')->with('success', 'We sent an OTP to your email.');
    }

    public function showOtpForm()
    {
        // Check if there's a pending registration
        if (!session('pending_user')) {
            return redirect()->route('register')->with('error', 'Please start the registration process again.');
        }
        
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|digits:4']);

        $pendingUser = session('pending_user');

        if (!$pendingUser) {
            return redirect()->route('register')->with('error', 'No pending registration found. Please register again.');
        }

        if ($request->otp == $pendingUser['otp']) {
            // Create user after OTP verified
            $user = User::create([
                'name'       => $pendingUser['name'],
                'cp_number'  => $pendingUser['cp_number'],
                'email'      => $pendingUser['email'],
                'password'   => $pendingUser['password'],
                'is_verified'=> true,
            ]);

            // Log successful registration
            Log::info('User registered successfully', ['user_id' => $user->id, 'email' => $user->email]);

            session()->forget('pending_user');

            return redirect()->route('welcome')->with('success', 'Account verified and created successfully!');
        }

        return back()->with('error', 'Invalid OTP, please try again.');
    }

    public function resendOtp()
    {
        $pendingUser = session('pending_user');

        if (!$pendingUser) {
            return redirect()->route('register')->with('error', 'No pending registration found.');
        }

        $otp = rand(1000, 9999);
        $pendingUser['otp'] = $otp;
        session(['pending_user' => $pendingUser]);

        try {
            Mail::raw("Your new OTP code is: {$otp}", function ($message) use ($pendingUser) {
                $message->to($pendingUser['email'])
                        ->subject('Your New OTP Code');
            });
            
            Log::info('OTP resent successfully', ['email' => $pendingUser['email']]);
            
        } catch (\Exception $e) {
            Log::error('Failed to resend OTP', ['email' => $pendingUser['email'], 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to resend OTP. Please try again.');
        }

        return back()->with('success', 'A new OTP has been sent to your email.');
    }
}