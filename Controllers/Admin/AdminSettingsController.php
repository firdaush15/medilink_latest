<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AdminSettingsController extends Controller
{
    /**
     * Show the admin settings page.
     */
    public function index()
    {
        $user  = Auth::user();
        $admin = $user->admin;

        return view('admin.admin_setting', compact('user', 'admin'));
    }

    /**
     * Update profile information (name, email, phone, department, address, photo).
     */
    public function updateProfile(Request $request)
    {
        $user  = Auth::user();
        $admin = $user->admin;

        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number'  => ['nullable', 'string', 'max:20'],
            'department'    => ['nullable', 'string', 'max:100'],
            'address'       => ['nullable', 'string', 'max:500'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        // Handle photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->profile_photo = $path;
        }

        // Update users table
        $user->name    = $request->name;
        $user->email   = $request->email;
        $user->address = $request->address;
        $user->save();

        // Update admins table
        if ($admin) {
            $admin->update([
                'phone_number' => $request->phone_number,
                'department'   => $request->department,
            ]);
        }

        return redirect()->route('admin.settings')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'      => ['required'],
            'password'              => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.settings')
            ->with('success', 'Password updated successfully.');
    }
}