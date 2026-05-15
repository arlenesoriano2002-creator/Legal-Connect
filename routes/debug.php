<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAccountController;

// Temporary route file for debugging
Route::post('/admin/staff/verify-otp', [AdminAccountController::class, 'verifyCode'])->name('admin.staff.verifyOtp');