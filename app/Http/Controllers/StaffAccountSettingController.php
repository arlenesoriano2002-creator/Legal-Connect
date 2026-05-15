<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Helpers\PerTabAuthHelper;

class StaffAccountSettingController extends Controller
{
    /**
     * Display the staff account settings page
     */
    public function index()
    {
        $user = PerTabAuthHelper::getTabUser();
        
        // Verify user has diffun_staff role
if (!in_array($user->role, ['diffun_staff', 'staff', 'secretary', 'clerk'])) {
    return $this->unauthorizedRedirect($user);
}
        
        return view('staff.staffAccountSetting', compact('user'));
    }

    /**
     * Update staff profile information
     */
    public function updateProfile(Request $request)
    {
        $user = PerTabAuthHelper::getTabUser();
        
        // Verify user has diffun_staff role
if (!in_array($user->role, ['diffun_staff', 'staff', 'secretary', 'clerk'])) {
    return $this->unauthorizedRedirect($user);
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

        // Handle image upload to public/staff_images
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($user->image) {
                $oldImagePath = public_path($user->image);
                // Handle both cases: with or without staff_images/ prefix
                if (!file_exists($oldImagePath) && strpos($user->image, 'staff_images/') !== 0) {
                    $oldImagePath = public_path('staff_images/' . $user->image);
                }
                
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            // Generate unique filename with timestamp and staff prefix
            $imageName = time() . '_staff_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            
            // Move image to public/staff_images directory
            $request->file('image')->move(public_path('staff_images'), $imageName);
            
            // Store the relative path in database (staff_images/filename.jpg)
            $user->image = 'staff_images/' . $imageName;
        }

        // Update user information
        $user->name = $request->name;
        $user->email = $request->email;
        $user->cp_number = $request->cp_number;
        $user->save();

        return redirect()->route('staff.account.settings')
            ->with('success', 'Profile updated successfully!');
    }
    /**
     * Update staff password
     */
    public function updatePassword(Request $request)
    {
        $user = PerTabAuthHelper::getTabUser();
        
        // Verify user is a staff member
        if (!in_array($user->role, ['diffun_staff', 'staff', 'secretary', 'clerk'])) {
            return $this->unauthorizedRedirect($user);
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

        return redirect()->route('staff.account.settings')
            ->with('success', 'Password updated successfully!');
    }

    /**
     * Helper method to redirect unauthorized users
     * 
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function unauthorizedRedirect($user)
    {
        // Map roles to their dashboard routes
        $dashboards = [
            'admin' => 'admindashboard',
            'superadmin' => 'admindashboard',
            'diffun_staff' => 'dashboardStaff',
            'staff' => 'dashboardStaff',
            'secretary' => 'dashboardStaff',  // Staff dashboard for secretary
            'clerk' => 'dashboardStaff',      // Staff dashboard for clerk
            'cordon_staff' => 'cordon.dashboard',
            'client' => 'welcome',
        ];

        $routeName = $dashboards[$user->role] ?? 'welcome';

        return redirect()
            ->route($routeName)
            ->with('error', 'You do not have permission to access staff account settings. You have been redirected to your dashboard.');
    }
}
