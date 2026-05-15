<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminLogin;
use App\Models\StaffUser;
use App\Models\LawOffice; // Added this to ensure we can use the LawOffice model
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AdminAccountController extends Controller
{
    // Show the admin account page
    public function show()
    {
        $authUser = Auth::user();

        if (!$authUser) {
            return redirect('/login')->with('error', 'Please log in to access this page.');
        }

        $user = AdminLogin::find($authUser->id);

        if (!$user) {
            $authUser->update(['active_status' => 0]);
            Auth::logout();
            return redirect('/login')->with('error', 'User not found.');
        }

        $staffUsers = StaffUser::whereIn('role', ['secretary', 'clerk', 'staff'])
                        ->with('lawOffice')
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('adminAccount', compact('user', 'staffUsers'));
    }

    // Create new staff user
    public function createStaff(Request $request)
    {
        try {
            \Log::info('=== CREATE STAFF START (NO-OTP) ===');

            $validated = $request->validate([
                'name'      => 'required|string|max:255',
                'email'     => 'required|email|unique:users,email',
                'cp_number' => 'required|string|max:20',
                'role'      => 'required|in:secretary,clerk,staff',
                'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],
                'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $lawOfficeId = Auth::user()->law_office_id;
            if (!$lawOfficeId) {
                return redirect()->back()->with('error', 'Unable to determine your assigned law office.');
            }

            // Handle image if uploaded
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imageName = time() . '_staff_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
                $request->file('image')->move(public_path('staff_images'), $imageName);
                $imagePath = 'staff_images/' . $imageName;
            }

            // FIX: Get the actual office record to store the name in 'law_office' column
            $officeRecord = LawOffice::find($lawOfficeId);

            $staff = StaffUser::create([
                'name' => $validated['name'],
                'address' => 'Staff Address', 
                'email' => $validated['email'],
                'cp_number' => $validated['cp_number'],
                'role' => $validated['role'],
                'law_office_id' => $lawOfficeId,
                'law_office' => $officeRecord ? $officeRecord->law_office : 'Unknown', // FIX APPLIED HERE
                'password' => bcrypt($validated['password']),
                'image' => $imagePath,
                'active_status' => 0,
                'email_verified_at' => now(),
                'is_verified' => 1,
                'email_otp' => null,
            ]);

            return redirect()->back()->with('success', 'Staff user created successfully!');
        } catch (\Exception $e) {
            \Log::error('Error in createStaff: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create staff user: ' . $e->getMessage())->withInput();
        }
    }

    // Update staff user
    public function updateStaff(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'cp_number' => 'nullable|string|max:20',
                'password' => ['nullable', Password::min(8)->mixedCase()->numbers()->symbols()],
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'role' => 'required|in:secretary,clerk,staff',
            ]);

            $lawOfficeId = Auth::user()->law_office_id;
            if (!$lawOfficeId) {
                return redirect()->back()->with('error', 'Unable to determine your assigned law office.');
            }

            $user = StaffUser::findOrFail($id);
            $user->name = $request->name;
            $user->email = $request->email;
            
            if ($request->filled('cp_number')) {
                $user->cp_number = $request->cp_number;
            }

            $user->role = $request->role;
            $user->law_office_id = $lawOfficeId;

            // FIX: Update the text name of the law office as well
            $officeRecord = LawOffice::find($lawOfficeId);
            $user->law_office = $officeRecord ? $officeRecord->law_office : $user->law_office;

            if ($request->filled('password')) {
                $user->password = bcrypt($request->password);
            }

            if ($request->hasFile('image')) {
                $imageName = time() . '_staff_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
                $request->file('image')->move(public_path('staff_images'), $imageName);
                $user->image = 'staff_images/' . $imageName;
            }

            $user->save();

            return redirect()->back()->with('success', 'Staff user updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Error updating staff user: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update staff user: ' . $e->getMessage())->withInput();
        }
    }

    public function deleteStaff($id)
    {
        $user = StaffUser::where('id', $id)
                    ->whereIn('role', ['secretary', 'clerk', 'staff'])
                    ->firstOrFail();

        if ($user->image && strpos($user->image, 'staff_images/') === 0 && file_exists(public_path($user->image))) {
            unlink(public_path($user->image));
        }
        
        $user->delete();

        return redirect()->back()->with('success', 'Staff user deleted successfully!');
    }

    public function searchStaff(Request $request)
    {
        $search = $request->get('search');
        
        $staffUsers = StaffUser::whereIn('role', ['secretary', 'clerk', 'staff'])
            ->when($search, function($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%')
                             ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $authUser = Auth::user();
        $user = AdminLogin::find($authUser->id);

        return view('adminAccount', compact('user', 'staffUsers', 'search'));
    }
}