<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAccountController;

// Minimal route for testing
Route::post('/test-verify-otp', [AdminAccountController::class, 'verifyCode'])->name('test.verifyOtp');