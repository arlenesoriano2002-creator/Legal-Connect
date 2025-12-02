<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AccountController extends Controller
{
    public function showProfile()
    {
        $user = Auth::user();

        if (!$user) {
            return view('adminAccount', ['error' => 'User profile could not be loaded.']);
        }

        return view('adminAccount', ['user' => $user]);
    }

    public function getAccountInfo()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        return response()->json([
            'name' => $user->name,
            'cp_number' => $user->cp_number,
            'email' => $user->email,
            'password' => $user->password, // Note: In production, you might not want to send the password
        ]);
    }
}


