<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class AccountUpdateController extends Controller
{
    // Show edit account form
    public function edit()
    {
        $user = Auth::user();
        return view('edit-account-modal', compact('user'));
    }
    // Update account information (excluding password)
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to update your account.'
            ], 401);
        }

        $request->merge([
            'name' => trim((string) $request->input('name')),
            'address' => trim((string) $request->input('address')),
            'cp_number' => trim((string) $request->input('cp_number')),
            'email' => trim((string) $request->input('email')),
        ]);

        // Validation rules
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'cp_number' => ['required', 'string', 'max:20', Rule::unique('users', 'cp_number')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'cp_number.unique' => 'This phone number is already in use.',
            'email.unique' => 'This email address is already in use.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please fix the highlighted fields and try again.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $validated = $validator->validated();

            // Update user data
            $user->name = $validated['name'];
            $user->address = $validated['address'];
            $user->cp_number = $validated['cp_number'];
            
            // Check if email is being changed
            if ($user->email !== $validated['email']) {
                $user->email = $validated['email'];
                $user->email_verified_at = null; // Require email verification again
            }
            
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Account updated successfully!',
                'user' => [
                    'name' => $user->name,
                    'address' => $user->address,
                    'cp_number' => $user->cp_number,
                    'email' => $user->email,
                ]
            ]);

        } catch (QueryException $e) {
            Log::error('Database error updating account: ' . $e->getMessage());

            $errors = [];

            if (str_contains($e->getMessage(), 'users_cp_number_unique')) {
                $errors['cp_number'] = ['This phone number is already in use.'];
            }

            if (str_contains($e->getMessage(), 'users_email_unique')) {
                $errors['email'] = ['This email address is already in use.'];
            }

            return response()->json([
            'success' => false,
            'message' => !empty($errors)
                ? 'Please fix the highlighted fields and try again.'
                : 'Unable to update your account right now. Please try again.',
            'errors' => $errors
        ], !empty($errors) ? 422 : 500);
    } catch (\Exception $e) {
        Log::error('Error updating account: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error updating account. Please try again.'
        ], 500);
    }
}

// Request password change - Send OTP
public function requestPasswordChange(Request $request)
{
    $validator = Validator::make($request->all(), [
        'new_password' => 'required|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $user = Auth::user();
    $emailToSend = $user->email;

    try {
        // Generate 6-digit OTP
        $otp = mt_rand(100000, 999999);
        
        Log::info('Generated OTP for ' . $emailToSend . ': ' . $otp);
        
        // Store OTP in database
        DB::table('password_resets')->updateOrInsert(
            ['email' => $emailToSend],
            [
                'token' => Hash::make($otp),
                'new_password' => Hash::make($request->new_password),
                'created_at' => Carbon::now()
            ]
        );

        Log::info('Attempting to send OTP email to: ' . $emailToSend);
        
        // Send OTP email (ALWAYS try to send, even in development)
        $emailSent = $this->sendOtpEmail($emailToSend, $otp, $user->name);
        
        if (!$emailSent) {
            Log::error('Failed to send OTP email to: ' . $emailToSend);
            
            // If email fails, show OTP for testing
            return response()->json([
                'success' => true,
               'message' => 'OTP successfully sent to your email, the otp expiers in 10 minutes.',
                'requires_otp' => true,
                'otp_display' => true,
                'otp' => $otp
            ]);
        }

        Log::info('OTP email sent successfully to: ' . $emailToSend);
        
        return response()->json([
            'success' => true,
            'message' => 'Verification code sent to ' . $emailToSend . '! Please check your inbox.',
            'requires_otp' => true
        ]);

    } catch (\Exception $e) {
        Log::error('Error in requestPasswordChange: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Error processing your request. Please try again.'
        ], 500);
    }
}
// Verify OTP and update password
public function verifyOtpAndUpdatePassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'otp' => 'required|digits:6',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $user = Auth::user();
    $emailToVerify = $user->email; // Use authenticated user's email
    
    // Get password reset record
    $passwordReset = DB::table('password_resets')
        ->where('email', $emailToVerify)
        ->first(); // Removed user_id condition

    if (!$passwordReset) {
        // Check session for OTP if email failed
        if (session()->has('password_change_otp')) {
            $sessionOtp = session('password_change_otp');
            $sessionTime = session('password_change_otp_time');
            
            if (Carbon::parse($sessionTime)->addMinutes(15)->isPast()) {
                session()->forget(['password_change_otp', 'password_change_otp_time']);
                return response()->json([
                    'success' => false,
                    'message' => 'Verification code has expired. Please request a new one.'
                ], 422);
            }
            
            if ($request->otp == $sessionOtp) {
                // Update password
                $user->password = Hash::make(session('password_change_new_password'));
                $user->save();
                
                session()->forget(['password_change_otp', 'password_change_otp_time', 'password_change_new_password']);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Password updated successfully!',
                    'requires_otp' => false
                ]);
            }
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No password change request found. Please request a password change first.'
        ], 422);
    }

    // Check if OTP is expired (15 minutes)
    if (Carbon::parse($passwordReset->created_at)->addMinutes(15)->isPast()) {
        // Clean up expired record
        DB::table('password_resets')->where('email', $emailToVerify)->delete();
        
        return response()->json([
            'success' => false,
            'message' => 'Verification code has expired. Please request a new one.'
        ], 422);
    }

    // Verify OTP
    if (!Hash::check($request->otp, $passwordReset->token)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid verification code. Please try again.'
        ], 422);
    }

   try {
        // Update password
        $user->password = $passwordReset->new_password;
        $user->save();

        // Clean up password reset record
        DB::table('password_resets')->where('email', $emailToVerify)->delete();

        // Send confirmation email
        $this->sendPasswordChangeConfirmation($user->email, $user->name);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully! A confirmation email has been sent.',
            'requires_otp' => false
        ]);

    } catch (\Exception $e) {
        Log::error('Error updating password: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error updating password. Please try again.'
        ], 500);
    }
}

private function sendOtpEmail($email, $otp, $name)
{
    try {
        \Log::info('=== OTP EMAIL DEBUG ===');
        \Log::info('To: ' . $email);
        \Log::info('OTP: ' . $otp);
        
        // Test with simple email first
        \Mail::raw("Your OTP code is: $otp\nThis code expires in 15 minutes.", function($message) use ($email) {
            $message->to($email)
                   ->subject('Verification Code for Password Change - Legal Connect')
                   ->from('cafirma.jerome2002@gmail.com', 'LegalConnect');
        });
        
        \Log::info('Simple OTP email sent successfully to: ' . $email);
        
        // Also try with template
        Mail::send('password-change-otp', [
            'otp' => $otp,
            'name' => $name,
            'email' => $email
        ], function ($message) use ($email) {
            $message->to($email)
                   ->subject('Verification Code for Password Change - Legal Connect')
                   ->from('cafirma.jerome2002@gmail.com', 'LegalConnect');
        });
        
        \Log::info('Template OTP email sent successfully to: ' . $email);
        return true;
        
    } catch (\Exception $e) {
        \Log::error('Error sending OTP email to ' . $email . ': ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return false;
    }
}
private function sendPasswordChangeConfirmation($email, $name)
{
    try {
        Mail::send('password-changed-confirmation', [
            'name' => $name,
            'email' => $email,
            'timestamp' => now()->format('F j, Y \a\t g:i A'),
            'ip_address' => request()->ip()
        ], function ($message) use ($email) {
            $message->to($email)
                   ->subject('Password Changed Successfully - Legal Connect');
        });
        
        return true;
    } catch (\Exception $e) {
        Log::error('Error sending password change confirmation email: ' . $e->getMessage());
        return false;
    }
}

// Resend OTP
public function resendOtp(Request $request)
{
    $user = Auth::user();
    $emailToResend = $user->email; // Use authenticated user's email
    
    // Get password reset record
    $passwordReset = DB::table('password_resets')
        ->where('email', $emailToResend)
        ->first(); // Removed user_id condition

    if (!$passwordReset) {
        return response()->json([
            'success' => false,
            'message' => 'No password change request found. Please request a password change first.'
        ], 422);
    }

    try {
        // Generate new 6-digit OTP
        $otp = mt_rand(100000, 999999);
        
        // Update OTP in database
        DB::table('password_resets')
            ->where('email', $emailToResend)
            ->update([ // Removed user_id condition
                'token' => Hash::make($otp),
                'created_at' => Carbon::now()
            ]);

        // Send OTP email
        $emailSent = $this->sendOtpEmail($emailToResend, $otp, $user->name);
        
        if (!$emailSent) {
            // Store in session for fallback
            session(['password_change_otp' => $otp]);
            session(['password_change_otp_time' => Carbon::now()]);
            
            return response()->json([
                'success' => true,
                'message' => 'Email service unavailable. Use this code: ' . $otp,
                'otp_display' => true,
                'otp' => $otp
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'New verification code sent to ' . $emailToResend . '!'
        ]);

    } catch (\Exception $e) {
        Log::error('Error resending OTP: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error resending verification code. Please try again.'
        ], 500);
    }
}
}
