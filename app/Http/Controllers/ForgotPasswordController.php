<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    // Show email input form
    public function showEmailForm()
    {
        return view('forgot-password.email');
    }

    // Send OTP to email
    public function sendOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email'
            ]);

            // Generate 4-digit OTP
            $otp = rand(1000, 9999);

            // Find user and update OTP
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return back()->withErrors(['email' => 'User not found!']);
            }

            $user->email_otp = $otp;
            $user->is_verified = 0;
            $user->save();

            // Send OTP via email
            $this->sendOtpEmail($user->email, $otp);

            return redirect()->route('password.otp')->with([
                'email' => $request->email, 
                'success' => 'The Code was sent to your email check it!'
            ]);

        } catch (\Exception $e) {
            Log::error('Send OTP Error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Something went wrong. Please try again.']);
        }
    }

    // Show OTP verification form
    public function showOtpForm()
    {
        if (!session('email')) {
            return redirect()->route('password.request');
        }
        
        return view('forgot-password.otp');
    }

    // Verify OTP
    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|digits:4'
            ]);

            $user = User::where('email', $request->email)
                        ->where('email_otp', $request->otp)
                        ->first();

            if ($user) {
                $user->is_verified = 1;
                $user->email_otp = null; // Clear OTP after verification
                $user->save();

                return redirect()->route('password.reset')->with([
                    'email' => $request->email, 
                    'success' => 'The code was verified successfully!'
                ]);
            }

            return back()->withErrors(['otp' => 'Invalid OTP code!']);

        } catch (\Exception $e) {
            Log::error('Verify OTP Error: ' . $e->getMessage());
            return back()->withErrors(['otp' => 'Something went wrong. Please try again.']);
        }
    }

    // Show password reset form
    public function showResetForm()
    {
        if (!session('email')) {
            return redirect()->route('password.request');
        }
        
        return view('forgot-password.reset');
    }

    // Reset password
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed'
            ]);

            $user = User::where('email', $request->email)
                        ->where('is_verified', 1)
                        ->first();

            if ($user) {
                $user->password = Hash::make($request->password);
                $user->is_verified = 0; // Reset verification status
                $user->save();

                return redirect()->route('login')->with('success', 'Password reset successfully!');
            }

            return back()->withErrors(['email' => 'Invalid request! Please start the process again.']);

        } catch (\Exception $e) {
            Log::error('Reset Password Error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Something went wrong. Please try again.']);
        }
    }

    // Private function to send OTP email
    private function sendOtpEmail($email, $otp)
    {
        try {
            $data = [
                'otp' => $otp,
                'email' => $email
            ];

            Mail::send('emails.otp', $data, function($message) use ($email) {
                $message->to($email)
                        ->subject('Your OTP Code - Legal Connect');
            });

        } catch (\Exception $e) {
            Log::error('Send OTP Email Error: ' . $e->getMessage());
            throw new \Exception('Failed to send OTP email');
        }
    }
}