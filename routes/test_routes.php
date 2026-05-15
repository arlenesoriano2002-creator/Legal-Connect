<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAccountController;

// Temporary route for debugging
Route::post('/admin/staff/verify-otp', [AdminAccountController::class, 'verifyStaffOtp'])->name('admin.staff.verifyOtp');