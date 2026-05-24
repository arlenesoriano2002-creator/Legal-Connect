<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Admin\SmsChatController;

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
            'address'       => 'required|string|max:255',
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
                'address'       => $request->address,
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
                // Ensure the SMTP authenticated account is used as the From address so Gmail allows sending
                $message->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($request->email)
                    ->subject('Your OTP Code');
            });
            
            // Log successful OTP sending
            Log::info('OTP sent successfully', ['email' => $request->email, 'otp' => $otp]);

            // Also attempt to send OTP via SMS (iprogsms) using the raw inputted phone number
            try {
                $smsController = new SmsChatController();

                $inputPhone = $request->input('phone_number', '');
                $countryCode = $request->input('country_code', '');

                // Prefer the raw inputted phone number as requested. Normalize to a local-ready format for provider.
                $apiPhone = '';
                $digits = preg_replace('/[^0-9]/', '', $inputPhone);

                if (strlen($digits) === 10) {
                    // e.g. 9123456789 -> local 09123456789
                    $apiPhone = '0' . $digits;
                } elseif (strlen($digits) === 11 && substr($digits, 0, 1) === '0') {
                    // already 0-prefixed
                    $apiPhone = $digits;
                } elseif (!empty($countryCode)) {
                    // fallback: combine country code and input then format
                    $fullPhone = $countryCode . $inputPhone;
                    $apiPhone = $smsController->formatPhoneForApi($fullPhone);
                } else {
                    // last-resort: pass digits as-is
                    $apiPhone = $digits;
                }

                $smsResp = $smsController->sendViaIprog($apiPhone, "Your OTP code is: {$otp}");
                Log::info('OTP SMS send attempt', ['input_phone' => $inputPhone, 'api_phone' => $apiPhone, 'response' => $smsResp]);
            } catch (\Exception $e) {
                Log::warning('Failed to send OTP via SMS: ' . $e->getMessage());
            }
            
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
                'address'    => $pendingUser['address'],
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
                // Use authenticated SMTP username as From to improve deliverability (Gmail requirement)
                $message->from(env('MAIL_USERNAME'), config('mail.from.name'))
                        ->to($pendingUser['email'])
                        ->subject('Your New OTP Code');
            });
            
            Log::info('OTP resent successfully', ['email' => $pendingUser['email']]);

            // Also try sending OTP via SMS if phone exists in session data (prefer raw phone_number)
            try {
                if (!empty($pendingUser['phone_number'])) {
                    $smsController = new SmsChatController();
                    $inputPhone = $pendingUser['phone_number'];
                    $countryCode = $pendingUser['country_code'] ?? '';

                    $digits = preg_replace('/[^0-9]/', '', $inputPhone);
                    if (strlen($digits) === 10) {
                        $apiPhone = '0' . $digits;
                    } elseif (strlen($digits) === 11 && substr($digits,0,1) === '0') {
                        $apiPhone = $digits;
                    } elseif (!empty($countryCode)) {
                        $apiPhone = $smsController->formatPhoneForApi($countryCode . $inputPhone);
                    } else {
                        $apiPhone = $digits;
                    }

                    $smsResp = $smsController->sendViaIprog($apiPhone, "Your new OTP code is: {$otp}");
                    Log::info('Resent OTP via SMS attempt', ['input_phone' => $inputPhone, 'api_phone' => $apiPhone, 'response' => $smsResp]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to resend OTP via SMS: ' . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to resend OTP', ['email' => $pendingUser['email'], 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to resend OTP. Please try again.');
        }

        return back()->with('success', 'A new OTP has been sent to your email.');
    }
}