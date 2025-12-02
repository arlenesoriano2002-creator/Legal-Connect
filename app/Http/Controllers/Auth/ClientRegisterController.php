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
        // Validate the request with new fields
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'country_code' => 'required|string',
            'phone_number' => 'required|digits_between:9,15',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'phone_number.required' => 'The phone number field is required.',
            'phone_number.digits_between' => 'The phone number must be between 9 and 15 digits.',
            'email.unique' => 'This email address is already registered.',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Combine country code and phone number for cp_number field
        $cp_number = $request->country_code . $request->phone_number;

        // Check if the combined phone number already exists
        if (User::where('cp_number', $cp_number)->exists()) {
            return redirect()->back()
                ->withErrors(['phone_number' => 'This phone number is already registered.'])
                ->withInput();
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'cp_number' => $cp_number, // Store combined number
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client', // Set default role
        ]);

        // Redirect to success page or login
        return redirect()->route('login')
            ->with('success', 'Registration successful! Please login.');
    }
}