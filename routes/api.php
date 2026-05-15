<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

Route::post('/send-verification-email', function (Request $request) {
    $email = $request->input('email');
    $code = $request->input('code');

    Mail::raw("Your verification code is: $code", function ($message) use ($email) {
        $message->to($email)->subject('Verification Code');
    });

    return response()->json(['success' => true]);
});

Route::post('/send-verification-sms', function (Request $request) {
    $phone = $request->input('phone');
    $code = $request->input('code');

    $response = Http::post(env('IPROG_API_URL'), [
        'api_key' => env('IPROG_API_KEY'),
        'number' => $phone,
        'message' => "Your verification code is: $code",
    ]);

    return response()->json(['success' => $response->successful()]);
});