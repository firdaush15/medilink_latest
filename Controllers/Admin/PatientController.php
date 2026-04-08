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
    // In Admin\PatientController.php index() method
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

        // Flagged filter - ADD DEBUGGING
        if ($request->filled('flagged')) {
            \Log::info('Flagged filter value:', ['flagged' => $request->flagged]);

            // ✅ FIX: Convert to boolean properly
            if ($request->flagged === '1') {
                $query->where('is_flagged', true);
            } elseif ($request->flagged === '0') {
                $query->where('is_flagged', false);
            }
        }

        $patients = $query->paginate(10)->withQueryString();

        // ✅ DEBUG: Check if any flagged patients exist
        \Log::info('Total patients:', ['total' => $patients->total()]);
        \Log::info('Flagged patients count:', [
            'count' => Patient::where('is_flagged', true)->count()
        ]);

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
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $patient = Patient::findOrFail($id);

        $patient->update([
            'is_flagged' => true,
            'flag_reason' => $validated['reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patient flagged successfully'
        ]);
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

        return response()->json([
            'success' => true,
            'message' => 'Flag removed successfully'
        ]);
    }
}
