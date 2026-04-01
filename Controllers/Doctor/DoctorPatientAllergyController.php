<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\PatientAllergy;
use Illuminate\Http\Request;

class DoctorPatientAllergyController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,patient_id',
            'allergy_type' => 'required|in:Drug/Medication,Food,Environmental,Other',
            'allergen_name' => 'required|string|max:255',
            'severity' => 'required|in:Mild,Moderate,Severe,Life-threatening',
            'reaction_description' => 'nullable|string',
            'onset_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        PatientAllergy::create([
            'patient_id' => $validated['patient_id'],
            'allergy_type' => $validated['allergy_type'],
            'allergen_name' => $validated['allergen_name'],
            'severity' => $validated['severity'],
            'reaction_description' => $validated['reaction_description'] ?? null,
            'onset_date' => $validated['onset_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => auth()->id(),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Patient allergy recorded successfully');
    }

    public function destroy($allergyId)
    {
        $allergy = PatientAllergy::findOrFail($allergyId);
        
        // Soft deactivate instead of delete
        $allergy->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Allergy deactivated successfully');
    }
}
