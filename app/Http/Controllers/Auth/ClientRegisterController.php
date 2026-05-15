<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ClientRegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'country_code' => 'required|string',
            'phone_number' => 'required|digits_between:9,15',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $cp_number = $request->country_code . $request->phone_number;

        if (User::where('cp_number', $cp_number)->exists()) {
            return back()
                ->withErrors(['phone_number' => 'This phone number is already registered.'])
                ->withInput();
        }

        // ✅ SAFE USER CREATION
        $user = User::create([
            'name' => $request->name,
            'cp_number' => $cp_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // ✅ FORCE SECURE VALUES
        $user->role = 'client';
        $user->is_verified = 0;
        $user->active_status = 0;
        $user->save();

        return redirect()->route('login')
            ->with('success', 'Registration successful! Please login.');
    }
}