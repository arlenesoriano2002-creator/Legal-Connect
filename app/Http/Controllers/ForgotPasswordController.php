<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Admin\SmsChatController;

class ForgotPasswordController extends Controller
{
    private const SESSION_EMAIL_KEY = 'password_reset_email';
    private const SESSION_VERIFIED_KEY = 'password_reset_verified';

    // Show email input form
    public function showEmailForm()
    {
        return view('forgot-password.email');
    }

    // Send OTP to email
    public function sendOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email'
            ]);

            // Generate 4-digit OTP
            $otp = rand(1000, 9999);

            // Find user and update OTP
            $user = User::where('email', $validated['email'])->first();
            
            if (!$user) {
                return back()->withErrors(['email' => 'User not found!']);
            }

            $user->email_otp = $otp;
            $user->is_verified = 0;
            $user->save();

            // Keep reset state in the session before any external delivery calls.
            session()->put([
                self::SESSION_EMAIL_KEY => $user->email,
                self::SESSION_VERIFIED_KEY => false,
                'email' => $user->email,
            ]);
            session()->save();

            $emailSent = $this->sendOtpEmail($user->email, $otp);
            $smsSent = $this->sendOtpSms($user, $otp);

            if (!$emailSent) {
                return back()
                    ->withInput()
                    ->withErrors(['email' => 'Unable to send the verification code by email. Please verify the SMTP settings and try again.']);
            }

            return redirect()->route('password.otp')->with([
                'email' => $user->email,
                'success' => $smsSent
                    ? 'The verification code was sent to your email and phone.'
                    : 'The verification code was sent to your email.'
            ]);

        } catch (\Exception $e) {
            Log::error('Send OTP Error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Something went wrong. Please try again.']);
        }
    }

    // Show OTP verification form
    public function showOtpForm()
    {
        if (!$this->getPasswordResetEmail()) {
            return redirect()->route('password.request');
        }
        
        return view('forgot-password.otp');
    }

    // Verify OTP
    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'otp' => 'required|digits:4'
            ]);

            $email = $request->input('email') ?: $this->getPasswordResetEmail();

            if (!$email) {
                return redirect()->route('password.request')
                    ->withErrors(['email' => 'Please start the password reset process again.']);
            }

            $user = User::where('email', $email)
                        ->where('email_otp', $request->otp)
                        ->first();

            if ($user) {
                $user->is_verified = 1;
                $user->email_otp = null; // Clear OTP after verification
                $user->save();

                session()->put([
                    self::SESSION_EMAIL_KEY => $email,
                    self::SESSION_VERIFIED_KEY => true,
                    'email' => $email,
                ]);
                session()->save();

                return redirect()->route('password.reset')->with([
                    'email' => $email,
                    'verified' => true,
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
        if (!$this->getPasswordResetEmail()) {
            return redirect()->route('password.request');
        }

        if (!session(self::SESSION_VERIFIED_KEY)) {
            return redirect()->route('password.otp')
                ->withErrors(['otp' => 'Please verify the code before resetting your password.']);
        }
        
        return view('forgot-password.reset');
    }

    // Reset password
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|min:8|confirmed'
            ]);

            $email = $request->input('email') ?: $this->getPasswordResetEmail();

            if (!$email) {
                return redirect()->route('password.request')
                    ->withErrors(['email' => 'Please start the password reset process again.']);
            }

            $user = User::where('email', $email)
                        ->where('is_verified', 1)
                        ->first();

            if ($user) {
                $user->password = Hash::make($request->password);
                $user->is_verified = 0; // Reset verification status
                $user->save();

                session()->forget([
                    self::SESSION_EMAIL_KEY,
                    self::SESSION_VERIFIED_KEY,
                    'email',
                ]);

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
        $fromAddress = config('mail.from.address') ?: config('mail.mailers.smtp.username');
        $fromName = config('mail.from.name');
        $data = [
            'otp' => $otp,
            'email' => $email
        ];

        try {
            Mail::mailer(config('mail.default'))->send('emails.otp', $data, function($message) use ($email, $fromAddress, $fromName) {
                if (!empty($fromAddress)) {
                    $message->from($fromAddress, $fromName);
                }

                $message->to($email)
                    ->subject('Your OTP Code - Legal Connect');
            });

            Log::info('Forgot-password OTP email sent successfully', ['email' => $email]);
            return true;
        } catch (\Exception $e) {
            Log::warning('Forgot-password template OTP email failed: ' . $e->getMessage(), ['email' => $email]);
        }

        try {
            Mail::mailer(config('mail.default'))->raw("Your OTP code is: {$otp}", function($message) use ($email, $fromAddress, $fromName) {
                if (!empty($fromAddress)) {
                    $message->from($fromAddress, $fromName);
                }

                $message->to($email)
                    ->subject('Your OTP Code - Legal Connect');
            });

            Log::info('Forgot-password OTP raw email fallback sent successfully', ['email' => $email]);
            return true;
        } catch (\Exception $e) {
            Log::error('Send OTP Email Error: ' . $e->getMessage(), ['email' => $email]);
            return false;
        }
    }

    private function sendOtpSms(User $user, int $otp): bool
    {
        if (empty($user->cp_number)) {
            Log::info('Forgot-password: user has no cp_number, skipping SMS', ['user_id' => $user->id]);
            return false;
        }

        try {
            $sms = new SmsChatController();
            $apiPhone = $sms->formatPhoneForApi($user->cp_number);

            if (empty($apiPhone)) {
                Log::warning('Forgot-password SMS skipped because phone normalization returned empty', [
                    'user_id' => $user->id,
                    'phone' => $user->cp_number,
                ]);
                return false;
            }

            $smsResp = $sms->sendViaIprog($apiPhone, "Your OTP code is: {$otp}");
            Log::info('Forgot-password OTP SMS attempt', [
                'user_id' => $user->id,
                'phone' => $user->cp_number,
                'api_phone' => $apiPhone,
                'response' => $smsResp,
            ]);

            return !empty($smsResp['success']);
        } catch (\Exception $e) {
            Log::warning('Failed to send forgot-password OTP via SMS: ' . $e->getMessage(), [
                'user_id' => $user->id,
            ]);
            return false;
        }
    }

    private function getPasswordResetEmail(): ?string
    {
        return session(self::SESSION_EMAIL_KEY) ?? session('email');
    }
}
