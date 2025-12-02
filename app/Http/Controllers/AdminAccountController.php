<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminLogin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminAccountController extends Controller
{
    // Show the admin account page
    public function show()
    {
        // Use default guard instead of 'admin_guard'
        $authUser = Auth::user();

        if (!$authUser) {
            return redirect('/login')->with('error', 'Please log in to access this page.');
        }

        // Always reload from DB, not cached session
        $user = AdminLogin::find($authUser->id);

        if (!$user) {
            Auth::logout();
            return redirect('/login')->with('error', 'User not found.');
        }

        // Get staff users
        $staffUsers = User::where('role', 'staff')->get();

        return view('adminAccount', compact('user', 'staffUsers'));
    }

    // Update admin profile
    public function update(Request $request)
    {
        $user = AdminLogin::findOrFail($request->id);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($user->image && file_exists(public_path($user->image))) {
                unlink(public_path($user->image));
            }
            
            $imageName = time() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads'), $imageName);
            $user->image = 'uploads/' . $imageName;
        }

        // Update other fields
        $user->username = $request->username;
        $user->email = $request->email;
        $user->cp_number = $request->cp_number;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        // Refresh the logged-in user
        Auth::login($user);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    // Create new staff user
    public function createStaff(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'cp_number' => 'required|string|max:20',
            'password' => 'required|string|min:6',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->cp_number = $request->cp_number;
        $user->password = bcrypt($request->password);
        $user->role = 'staff';
        $user->is_verified = true;

        // Handle image upload
        if ($request->hasFile('image')) {
            $imageName = time() . '_staff.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads'), $imageName);
            $user->image = 'uploads/' . $imageName;
        }

        $user->save();

        return redirect()->back()->with('success', 'Staff user created successfully!');
    }

    // Update staff user
    public function updateStaff(Request $request, $id)
    {
        $user = User::where('id', $id)->where('role', 'staff')->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'cp_number' => 'required|string|max:20',
            'password' => 'nullable|string|min:6',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->cp_number = $request->cp_number;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($user->image && file_exists(public_path($user->image))) {
                unlink(public_path($user->image));
            }
            
            $imageName = time() . '_staff.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads'), $imageName);
            $user->image = 'uploads/' . $imageName;
        }

        $user->save();

        return redirect()->back()->with('success', 'Staff user updated successfully!');
    }

    // Delete staff user
    public function deleteStaff($id)
    {
        $user = User::where('id', $id)->where('role', 'staff')->firstOrFail();

        // Delete image if exists
        if ($user->image && file_exists(public_path($user->image))) {
            unlink(public_path($user->image));
        }

        $user->delete();

        return redirect()->back()->with('success', 'Staff user deleted successfully!');
    }

    // Search staff users
    public function searchStaff(Request $request)
    {
        $search = $request->get('search');
        
        $staffUsers = User::where('role', 'staff')
            ->when($search, function($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->get();

        $authUser = Auth::user();
        $user = AdminLogin::find($authUser->id);

        return view('adminAccount', compact('user', 'staffUsers', 'search'));
    }
}