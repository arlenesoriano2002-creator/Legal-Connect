<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
   public function customLogin(Request $request)
{
    $credentials = $request->only('username', 'password');

    if (Auth::attempt(['name' => $credentials['username'], 'password' => $credentials['password']])) {
        $user = Auth::user();

        if ($user->id === 1) {
            return redirect('/superadministrator');  // matches route
        } elseif ($user->id === 2) {
            return redirect()->route('admin.page');
        } else {
            return redirect()->route('welcome');
        }

    }

    return redirect()->back()->withErrors(['Invalid credentials']);
}

}
