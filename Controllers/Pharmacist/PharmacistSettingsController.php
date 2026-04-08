<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class PharmacistSettingsController extends Controller
{
    /**
     * Show the pharmacist settings page.
     */
    public function index()
    {
        $user       = Auth::user();
        $pharmacist = $user->pharmacist;

        return view('pharmacist.pharmacist_setting', compact('user', 'pharmacist'));
    }

    /**
     * Update profile information.
     */
    public function updateProfile(Request $request)
    {
        $user       = Auth::user();
        $pharmacist = $user->pharmacist;

        $request->validate([
            'first_name'     => ['required', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'email'          => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number'   => ['nullable', 'string', 'max:20'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'specialization' => ['nullable', 'string', 'max:100'],
            'address'        => ['nullable', 'string', 'max:500'],
            'profile_photo'  => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        // Handle photo upload
        if ($request->hasFile('profile_photo')) {
            if ($pharmacist?->profile_photo) {
                Storage::disk('public')->delete($pharmacist->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');

            if ($pharmacist) {
                $pharmacist->profile_photo = $path;
                $pharmacist->save();
            }
        }

        // Update users table (name is first + last)
        $user->name    = $request->first_name . ' ' . $request->last_name;
        $user->email   = $request->email;
        $user->address = $request->address;
        $user->save();

        // Update pharmacists table
        if ($pharmacist) {
            $pharmacist->update([
                'first_name'     => $request->first_name,
                'last_name'      => $request->last_name,
                'phone_number'   => $request->phone_number,
                'license_number' => $request->license_number,
                'specialization' => $request->specialization,
            ]);
        }

        return redirect()->route('pharmacist.setting')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('pharmacist.setting')
            ->with('success', 'Password updated successfully.');
    }

    /**
     * Update availability status.
     */
    public function updateAvailability(Request $request)
    {
        $request->validate([
            'availability_status' => ['required', 'in:Available,On Break,On Leave,Unavailable'],
        ]);

        $pharmacist = Auth::user()->pharmacist;

        if ($pharmacist) {
            $pharmacist->update([
                'availability_status' => $request->availability_status,
            ]);
        }

        return redirect()->route('pharmacist.setting')
            ->with('success', 'Availability status updated.');
    }
}