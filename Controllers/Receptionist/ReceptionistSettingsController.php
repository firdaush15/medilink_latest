<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ReceptionistSettingsController extends Controller
{
    /**
     * Show the receptionist settings page.
     */
    public function index()
    {
        $user         = Auth::user();
        $receptionist = $user->receptionist;
        $performance  = $receptionist?->getTodayPerformance() ?? [
            'total_checked_in'    => 0,
            'on_time'             => 0,
            'late'                => 0,
            'on_time_percentage'  => 0,
        ];

        return view('receptionist.receptionist_setting', compact('user', 'receptionist', 'performance'));
    }

    /**
     * Update profile information.
     */
    public function updateProfile(Request $request)
    {
        $user         = Auth::user();
        $receptionist = $user->receptionist;

        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number'  => ['nullable', 'string', 'max:20'],
            'department'    => ['nullable', 'string', 'max:100'],
            'shift'         => ['nullable', 'in:Morning,Afternoon,Evening,Night,Rotating'],
            'address'       => ['nullable', 'string', 'max:500'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        // Handle photo upload
        if ($request->hasFile('profile_photo')) {
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

        // Update receptionists table
        if ($receptionist) {
            $receptionist->update([
                'phone_number' => $request->phone_number,
                'department'   => $request->department,
                'shift'        => $request->shift,
            ]);
        }

        return redirect()->route('receptionist.setting')
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

        return redirect()->route('receptionist.setting')
            ->with('success', 'Password updated successfully.');
    }
}