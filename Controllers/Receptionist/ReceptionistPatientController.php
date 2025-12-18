<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReceptionistPatientController extends Controller
{
    public function register()
    {
        // Get recently registered patients (last 5)
        $recentPatients = Patient::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('receptionist.receptionist_patientRegistration', compact('recentPatients'));
    }

    public function store(Request $request)
    {
        // Validate the input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:Male,Female,Other',
            'emergency_contact' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'data_consent' => 'required|accepted',
        ]);

        try {
            DB::beginTransaction();

            // Generate a secure random password
            $temporaryPassword = Str::random(12);

            // Create user account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($temporaryPassword),
                'role' => 'patient',
                'address' => $validated['address'] ?? null,
            ]);

            // Create patient record
            $patient = Patient::create([
                'user_id' => $user->id,
                'phone_number' => $validated['phone_number'],
                'gender' => $validated['gender'],
                'date_of_birth' => $validated['date_of_birth'],
                'emergency_contact' => $validated['emergency_contact'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('receptionist.patients.register')
                ->with('success', "Patient registered successfully! Patient ID: P" . str_pad($patient->patient_id, 4, '0', STR_PAD_LEFT) . ". Temporary password: {$temporaryPassword} (Please send this to patient's email)");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $patients = Patient::with('user')
            ->whereHas('user', function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->orWhere('phone_number', 'like', "%{$query}%")
            ->orWhere('patient_id', $query)
            ->limit(10)
            ->get();

        return response()->json($patients);
    }
}