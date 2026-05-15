<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Helpers\PerTabAuthHelper;

class AdminAccountSettingController extends Controller
{
    /**
     * Display the admin account settings page
     */
    public function index()
    {
        $user = PerTabAuthHelper::getTabUser(); // get logged-in admin

        // Verify user is admin or superadmin
        if (!in_array($user->role, ['admin', 'superadmin', 'lawyer'])) {
            return $this->unauthorizedRedirect($user);
        }

        return view('admin-account-setting.adminAccountSetting', compact('user'));
    }

    /**
     * Update admin profile information
     */
    public function updateProfile(Request $request)
    {
        $user = PerTabAuthHelper::getTabUser();
        
        // Check if user is admin
        if (!$user || !in_array($user->role, ['admin', 'superadmin', 'lawyer'])) {
            abort(403, 'Unauthorized access.');
        }
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'cp_number' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($user->image && Storage::exists('public/' . $user->image)) {
                Storage::delete('public/' . $user->image);
            }
            
            // Store new image
            $imagePath = $request->file('image')->store('uploads/admin/profile', 'public');
            $user->image = $imagePath;
        }

        // Update user information
        $user->name = $request->name;
        $user->email = $request->email;
        $user->cp_number = $request->cp_number;
        $user->save();

        return redirect()->route('admin.account.settings')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Update admin password
     */
    public function updatePassword(Request $request)
    {
        $user = PerTabAuthHelper::getTabUser();
        
        // Check if user is admin
        if (!$user || !in_array($user->role, ['admin', 'superadmin', 'lawyer'])) {
            abort(403, 'Unauthorized access.');
        }
        
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->with('error', 'Current password is incorrect.');
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('admin.account.settings')
            ->with('success', 'Password updated successfully!');
    }

    /**
     * Delete admin account
     */
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        
        // Check if user is admin
        if (!$user || !in_array($user->role, ['admin', 'superadmin', 'lawyer'])) {
            abort(403, 'Unauthorized access.');
        }
        
        $validator = Validator::make($request->all(), [
            'confirm_email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        // Verify email matches
        if ($request->confirm_email !== $user->email) {
            return redirect()->back()
                ->with('error', 'Email does not match your account.');
        }

        // Delete user image if exists
        if ($user->image && Storage::exists('public/' . $user->image)) {
            Storage::delete('public/' . $user->image);
        }

        // Logout user
        Auth::logout();
        
        // Delete user
        $user->delete();

        return redirect('/login')
            ->with('success', 'Your account has been deleted successfully.');
    }

    /**
     * Helper method to redirect unauthorized users
     * 
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function unauthorizedRedirect($user)
    {
        // Map roles to their dashboard routes (use valid route names)
        $dashboards = [
            'admin' => 'admindashboard',
            'superadmin' => 'admindashboard',
            'lawyer' => 'admindashboard',
            'secretary' => 'dashboardStaff',
            'clerk' => 'dashboardStaff',
            'diffun_staff' => 'dashboardStaff',
            'cordon_staff' => 'cordon.dashboard',
            'client' => 'welcome',
        ];

        $routeName = $dashboards[$user->role] ?? 'welcome';

        return redirect()
            ->route($routeName)
            ->with('error', 'You do not have permission to access admin account settings. You have been redirected to your dashboard.');
    }
}
