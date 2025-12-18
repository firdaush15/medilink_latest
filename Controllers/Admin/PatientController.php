<?php

// ============================================
// 2. Admin\PatientController.php
// ============================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::with('user');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Flagged filter
        if ($request->filled('flagged')) {
            $query->where('is_flagged', $request->flagged);
        }

        $patients = $query->paginate(10)->withQueryString();

        return view('admin.admin_managePatients', compact('patients'));
    }

    /**
     * View patient details (READ-ONLY medical info)
     */
    public function show($id)
    {
        $patient = Patient::with(['user', 'appointments', 'medicalRecords', 'prescriptions'])->findOrFail($id);
        
        $medicalRecords = $patient->medicalRecords()->with('doctor.user')->get();
        $prescriptions = $patient->prescriptions()->with(['doctor.user', 'items'])->get();
        
        return view('admin.admin_patientDetails', compact('patient', 'medicalRecords', 'prescriptions'));
    }

    /**
     * Edit patient contact information (NOT medical data)
     */
    public function edit($id)
    {
        $patient = Patient::with('user')->findOrFail($id);
        return view('admin.patients.edit', compact('patient'));
    }

    /**
     * Update patient contact info
     */
    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $validated = $request->validate([
            'phone_number' => 'required|string',
            'emergency_contact' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $patient->update($validated);

        // Update user email if provided
        if ($request->filled('email')) {
            $patient->user->update(['email' => $request->email]);
        }

        return redirect()->route('admin.patients')->with('success', 'Patient information updated');
    }

    /**
     * Flag patient (for no-shows, payment issues, etc.)
     */
    public function flag(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);
        
        $patient->update([
            'is_flagged' => true,
            'flag_reason' => $request->reason,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Remove flag from patient
     */
    public function unflag($id)
    {
        $patient = Patient::findOrFail($id);
        
        $patient->update([
            'is_flagged' => false,
            'flag_reason' => null,
        ]);

        return response()->json(['success' => true]);
    }
}
