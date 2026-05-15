<?php

namespace App\Http\Controllers;

use App\Models\LawOffice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuperAdminController extends Controller
{
    public function lawyers()
    {
        $lawyers = User::query()
            ->with('lawOffice')
            ->select([
                'id',
                'image',
                'name',
                'address',
                'username',
                'cp_number',
                'email',
                'role',
                'law_office',
                'law_office_id',
            ])
            ->where('role', 'lawyer')
            ->orderBy('name')
            ->get();

        $lawOffices = LawOffice::query()
            ->select('id', 'law_office')
            ->orderBy('law_office')
            ->get();

        return view('superadmin.lawyers', compact('lawyers', 'lawOffices'));
    }

    public function storeLawyer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'cp_number' => ['required', 'regex:/^\d{11}$/', 'unique:users,cp_number'],
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],
            'law_office_id' => 'required|integer|exists:law_offices,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        Log::info('storeLawyer validated', $validated);

        $lawyer = new User();
        $lawyer->name = $validated['name'];
        $lawyer->address = $validated['address'];
        $lawyer->username = $validated['username'];
        $lawyer->cp_number = $validated['cp_number'];
        $lawyer->email = $validated['email'];
        $lawyer->password = $validated['password'];
        $lawyer->law_office_id = $validated['law_office_id'];
        $lawyer->law_office = LawOffice::find($validated['law_office_id'])->law_office ?? '';
        $lawyer->role = 'lawyer';
        $lawyer->active_status = 0;
        $lawyer->is_verified = 1;
        $lawyer->email_verified_at = now();
        $lawyer->email_otp = null;

        if ($request->hasFile('image')) {
            $lawyer->image = $request->file('image')->store('uploads/lawyers', 'public');
        }

        try {
            $saved = $lawyer->save();
            Log::info('Lawyer saved', ['saved' => $saved, 'lawyer_id' => $lawyer->id, 'law_office_id' => $lawyer->law_office_id]);
        } catch (\Exception $e) {
            Log::error('Failed to save lawyer', ['error' => $e->getMessage(), 'lawyer' => $lawyer->toArray()]);
            return redirect()
                ->route('superadmin.lawyers')
                ->with('error', 'Failed to save lawyer: ' . $e->getMessage());
        }

        return redirect()
            ->route('superadmin.lawyers')
            ->with('success', 'Lawyer record created successfully.');
    }

    public function updateLawyer(Request $request, User $lawyer)
    {
        abort_unless($lawyer->role === 'lawyer', 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($lawyer->id)],
            'cp_number' => ['required', 'regex:/^\d{11}$/', Rule::unique('users', 'cp_number')->ignore($lawyer->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($lawyer->id)],
            'password' => ['nullable', Password::min(8)->mixedCase()->numbers()->symbols()],
            'law_office_id' => 'required|integer|exists:law_offices,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        Log::info('updateLawyer validated', $validated);
        $lawyer->name = $validated['name'];
        $lawyer->address = $validated['address'];
        $lawyer->username = $validated['username'];
        $lawyer->cp_number = $validated['cp_number'];
        $lawyer->email = $validated['email'];
        $lawyer->law_office_id = $validated['law_office_id'];
        $lawyer->law_office = LawOffice::find($validated['law_office_id'])->law_office ?? '';
        $lawyer->role = 'lawyer';

        if (!empty($validated['password'])) {
            $lawyer->password = $validated['password'];
        }

        if ($request->hasFile('image')) {
            $lawyer->image = $request->file('image')->store('uploads/lawyers', 'public');
        }

        try {
            $saved = $lawyer->save();
            Log::info('Lawyer updated', ['saved' => $saved, 'lawyer_id' => $lawyer->id, 'law_office_id' => $lawyer->law_office_id]);
        } catch (\Exception $e) {
            Log::error('Failed to update lawyer', ['error' => $e->getMessage(), 'lawyer' => $lawyer->toArray()]);
            return redirect()
                ->route('superadmin.lawyers')
                ->with('error', 'Failed to update lawyer: ' . $e->getMessage());
        }

        return redirect()
            ->route('superadmin.lawyers')
            ->with('success', 'Lawyer record updated successfully.');
    }

    public function deleteLawyer(User $lawyer)
    {
        abort_unless($lawyer->role === 'lawyer', 404);

        $lawyer->delete();

        return redirect()
            ->route('superadmin.lawyers')
            ->with('success', 'Lawyer record deleted successfully.');
    }

    public function lawOffices()
    {
        $offices = LawOffice::query()
            ->orderBy('law_office')
            ->orderBy('lawyer')
            ->get();

        return view('superadmin.lawoffices', compact('offices'));
    }

    public function storeLawOffice(Request $request)
    {
        $validated = $request->validate([
            'lawyer' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'law_office' => 'required|string|max:255',
        ]);

        LawOffice::create($validated);

        return redirect()
            ->route('superadmin.lawoffices')
            ->with('success', 'Law office record created successfully.');
    }

    public function updateLawOffice(Request $request, LawOffice $lawOffice)
    {
        $validated = $request->validate([
            'lawyer' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'law_office' => 'required|string|max:255',
        ]);

        $lawOffice->update($validated);

        return redirect()
            ->route('superadmin.lawoffices')
            ->with('success', 'Law office record updated successfully.');
    }

    public function destroyLawOffice(LawOffice $lawOffice)
    {
        $lawOffice->delete();

        return redirect()
            ->route('superadmin.lawoffices')
            ->with('success', 'Law office record deleted successfully.');
    }

    public function clients()
    {
        $clients = User::query()
            ->select([
                'id',
                'name',
                'address',
                'cp_number',
                'email',
                'role',
                'law_office', // ✅ ADDED
            ])
            ->where('role', 'client')
            ->orderBy('name')
            ->get();

        return view('superadmin.clients', compact('clients'));
    }

    public function updateClient(Request $request, User $client)
    {
        abort_unless($client->role === 'client', 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'cp_number' => ['required', 'regex:/^\d{11}$/', Rule::unique('users', 'cp_number')->ignore($client->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($client->id)],
            'password' => ['nullable', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $client->name = $validated['name'];
        $client->address = $validated['address'];
        $client->cp_number = $validated['cp_number'];
        $client->email = $validated['email'];

        if (!empty($validated['password'])) {
            $client->password = $validated['password'];
        }

        $client->save();

        return redirect()
            ->route('superadmin.clients')
            ->with('success', 'Client record updated successfully.');
    }

    public function destroyClient(User $client)
    {
        abort_unless($client->role === 'client', 404);

        $client->delete();

        return redirect()
            ->route('superadmin.clients')
            ->with('success', 'Client record deleted successfully.');
    }

    public function messageInquiries()
    {
        $inquiries = DB::table('concerns_inquiries_message')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('superadmin.message-inquiry', compact('inquiries'));
    }

    // SECRETARY LAWYER MANAGEMENT METHODS
    public function secretaryLawyers()
    {
        // Check if user is secretary
        $user = auth()->user();
        if (!$user || $user->role !== 'secretary') {
            abort(403, 'Unauthorized access.');
        }

        // Get only lawyers from the secretary's law office
        $lawyers = User::query()
            ->with('lawOffice')
            ->select([
                'id',
                'image',
                'name',
                'address',
                'username',
                'cp_number',
                'email',
                'role',
                'law_office',
                'law_office_id',
            ])
            ->where('role', 'lawyer')
            ->where('law_office_id', $user->law_office_id) // Only show lawyers from secretary's office
            ->orderBy('name')
            ->get();

        return view('secretary.lawyers', compact('lawyers'));
    }

    public function storeSecretaryLawyer(Request $request)
    {
        // Check if user is secretary
        $user = auth()->user();
        if (!$user || $user->role !== 'secretary') {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'cp_number' => ['required', 'regex:/^\d{11}$/', 'unique:users,cp_number'],
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        Log::info('storeSecretaryLawyer validated', $validated);

        $lawyer = new User();
        $lawyer->name = $validated['name'];
        $lawyer->address = $validated['address'];
        $lawyer->username = $validated['username'];
        $lawyer->cp_number = $validated['cp_number'];
        $lawyer->email = $validated['email'];
        $lawyer->password = $validated['password'];
        
        // Automatically assign secretary's law office
        $lawyer->law_office_id = $user->law_office_id;
        $lawyer->law_office = $user->law_office;
        
        $lawyer->role = 'lawyer';
        $lawyer->active_status = 0;
        $lawyer->is_verified = 1;
        $lawyer->email_verified_at = now();
        $lawyer->email_otp = null;

        if ($request->hasFile('image')) {
            $lawyer->image = $request->file('image')->store('uploads/lawyers', 'public');
        }

        try {
            $saved = $lawyer->save();
            Log::info('Secretary lawyer saved', ['saved' => $saved, 'lawyer_id' => $lawyer->id, 'law_office_id' => $lawyer->law_office_id, 'secretary_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('Failed to save secretary lawyer', ['error' => $e->getMessage(), 'lawyer' => $lawyer->toArray()]);
            return redirect()
                ->route('secretary.lawyers')
                ->with('error', 'Failed to save lawyer: ' . $e->getMessage());
        }

        return redirect()
            ->route('secretary.lawyers')
            ->with('success', 'Lawyer record created successfully.');
    }

    // Secretaries Management
    public function secretaries()
    {
        $secretaries = User::query()
            ->with('lawOffice')
            ->select([
                'id',
                'image',
                'name',
                'address',
                'username',
                'cp_number',
                'email',
                'role',
                'law_office',
                'law_office_id',
            ])
            ->where('role', 'secretary')
            ->orderBy('name')
            ->get();

        $lawOffices = LawOffice::query()
            ->select('id', 'law_office')
            ->orderBy('law_office')
            ->get();

        return view('superadmin.secretaries', compact('secretaries', 'lawOffices'));
    }

    public function storeSecretary(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'cp_number' => ['required', 'regex:/^\d{11}$/', 'unique:users,cp_number'],
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],
            'law_office_id' => 'required|integer|exists:law_offices,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        Log::info('storeSecretary validated', $validated);

        $secretary = new User();
        $secretary->name = $validated['name'];
        $secretary->address = $validated['address'];
        $secretary->username = $validated['username'];
        $secretary->cp_number = $validated['cp_number'];
        $secretary->email = $validated['email'];
        $secretary->password = $validated['password'];
        $secretary->law_office_id = $validated['law_office_id'];
        $secretary->law_office = LawOffice::find($validated['law_office_id'])->law_office ?? '';
        $secretary->role = 'secretary';
        $secretary->active_status = 0;
        $secretary->is_verified = 1;
        $secretary->email_verified_at = now();
        $secretary->email_otp = null;

        if ($request->hasFile('image')) {
            $secretary->image = $request->file('image')->store('uploads/secretaries', 'public');
        }

        try {
            $saved = $secretary->save();
            Log::info('Secretary saved', ['saved' => $saved, 'secretary_id' => $secretary->id, 'law_office_id' => $secretary->law_office_id]);
        } catch (\Exception $e) {
            Log::error('Failed to save secretary', ['error' => $e->getMessage(), 'secretary' => $secretary->toArray()]);
            return redirect()
                ->route('superadmin.secretaries')
                ->with('error', 'Failed to save secretary: ' . $e->getMessage());
        }

        return redirect()
            ->route('superadmin.secretaries')
            ->with('success', 'Secretary record created successfully.');
    }

    public function updateSecretary(Request $request, User $secretary)
    {
        abort_unless($secretary->role === 'secretary', 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($secretary->id)],
            'cp_number' => ['required', 'regex:/^\d{11}$/', Rule::unique('users', 'cp_number')->ignore($secretary->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($secretary->id)],
            'password' => ['nullable', Password::min(8)->mixedCase()->numbers()->symbols()],
            'law_office_id' => 'required|integer|exists:law_offices,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $secretary->name = $validated['name'];
        $secretary->address = $validated['address'];
        $secretary->username = $validated['username'];
        $secretary->cp_number = $validated['cp_number'];
        $secretary->email = $validated['email'];
        $secretary->law_office_id = $validated['law_office_id'];
        $secretary->law_office = LawOffice::find($validated['law_office_id'])->law_office ?? '';

        if (!empty($validated['password'])) {
            $secretary->password = $validated['password'];
        }

        if ($request->hasFile('image')) {
            $secretary->image = $request->file('image')->store('uploads/secretaries', 'public');
        }

        $secretary->save();

        return redirect()
            ->route('superadmin.secretaries')
            ->with('success', 'Secretary record updated successfully.');
    }

    public function deleteSecretary(User $secretary)
    {
        abort_unless($secretary->role === 'secretary', 404);

        $secretary->delete();

        return redirect()
            ->route('superadmin.secretaries')
            ->with('success', 'Secretary record deleted successfully.');
    }

    public function updateSecretaryLawyer(Request $request, User $lawyer)
    {
        // Check if user is secretary
        $user = auth()->user();
        if (!$user || $user->role !== 'secretary') {
            abort(403, 'Unauthorized access.');
        }

        // Ensure secretary can only update lawyers from their office
        if ($lawyer->law_office_id !== $user->law_office_id) {
            abort(403, 'You can only manage lawyers from your law office.');
        }

        abort_unless($lawyer->role === 'lawyer', 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($lawyer->id)],
            'cp_number' => ['required', 'regex:/^\d{11}$/', Rule::unique('users', 'cp_number')->ignore($lawyer->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($lawyer->id)],
            'password' => ['nullable', Password::min(8)->mixedCase()->numbers()->symbols()],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $lawyer->name = $validated['name'];
        $lawyer->address = $validated['address'];
        $lawyer->username = $validated['username'];
        $lawyer->cp_number = $validated['cp_number'];
        $lawyer->email = $validated['email'];

        if (!empty($validated['password'])) {
            $lawyer->password = $validated['password'];
        }

        if ($request->hasFile('image')) {
            $lawyer->image = $request->file('image')->store('uploads/lawyers', 'public');
        }

        try {
            $lawyer->save();
            Log::info('Secretary lawyer updated', ['lawyer_id' => $lawyer->id, 'secretary_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('Failed to update secretary lawyer', ['error' => $e->getMessage(), 'lawyer_id' => $lawyer->id]);
            return redirect()
                ->route('secretary.lawyers')
                ->with('error', 'Failed to update lawyer: ' . $e->getMessage());
        }

        return redirect()
            ->route('secretary.lawyers')
            ->with('success', 'Lawyer record updated successfully.');
    }
}
