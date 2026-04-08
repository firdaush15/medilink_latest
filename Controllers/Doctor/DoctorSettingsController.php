<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class DoctorSettingsController extends Controller
{
    /**
     * Show the doctor settings page.
     */
    public function index()
    {
        $user   = Auth::user();
        $doctor = $user->doctor;

        return view('doctor.doctor_setting', compact('user', 'doctor'));
    }

    /**
     * Update profile information.
     */
    public function updateProfile(Request $request)
    {
        $user   = Auth::user();
        $doctor = $user->doctor;

        $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number'        => ['nullable', 'string', 'max:20'],
            'specialization'      => ['nullable', 'string', 'max:100'],
            'address'             => ['nullable', 'string', 'max:500'],
            'profile_photo'       => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        // Handle photo upload
        if ($request->hasFile('profile_photo')) {
            if ($doctor?->profile_photo) {
                Storage::disk('public')->delete($doctor->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');

            if ($doctor) {
                $doctor->profile_photo = $path;
                $doctor->save();
            }
        }

        // Update users table
        $user->name    = $request->name;
        $user->email   = $request->email;
        $user->address = $request->address;
        $user->save();

        // Update doctors table
        if ($doctor) {
            $doctor->update([
                'phone_number'   => $request->phone_number,
                'specialization' => $request->specialization,
            ]);
        }

        return redirect()->route('doctor.setting')
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

        return redirect()->route('doctor.setting')
            ->with('success', 'Password updated successfully.');
    }

    /**
     * Update availability status.
     */
    public function updateAvailability(Request $request)
    {
        $request->validate([
            'availability_status' => ['required', 'in:Available,On Leave,Unavailable'],
        ]);

        $doctor = Auth::user()->doctor;

        if ($doctor) {
            $doctor->update([
                'availability_status' => $request->availability_status,
            ]);
        }

        return redirect()->route('doctor.setting')
            ->with('success', 'Availability status updated.');
    }
}