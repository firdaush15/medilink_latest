<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\DiagnosisCode;
use App\Models\PatientDiagnosis;
use App\Models\DiagnosisSymptom;
use App\Models\Appointment;

class DoctorDiagnosisController extends Controller
{
    /**
     * Search diagnoses for autocomplete
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $diagnoses = DiagnosisCode::active()
            ->where(function($q) use ($query) {
                $q->where('diagnosis_name', 'LIKE', "%{$query}%")
                  ->orWhere('icd10_code', 'LIKE', "%{$query}%")
                  ->orWhere('category', 'LIKE', "%{$query}%");
            })
            ->orderBy('diagnosis_name')
            ->limit(10)
            ->get();

        return response()->json($diagnoses);
    }

    /**
     * Store new diagnosis
     */
    public function store(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();

        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,appointment_id',
            'patient_id' => 'required|exists:patients,patient_id',
            'diagnosis_code_id' => 'required|exists:diagnosis_codes,diagnosis_code_id',
            'diagnosis_type' => 'required|in:Primary,Secondary,Differential,Ruled Out',
            'certainty' => 'required|in:Confirmed,Probable,Suspected',
            'diagnosis_date' => 'required|date',
            'clinical_notes' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'status' => 'required|in:Active,Resolved,Chronic,Under Treatment',
            'requires_referral' => 'nullable|boolean',
            'referral_to' => 'nullable|required_if:requires_referral,1|string',
            'symptoms' => 'nullable|array',
            'symptoms.*.name' => 'required_with:symptoms|string',
            'symptoms.*.severity' => 'required_with:symptoms|in:Mild,Moderate,Severe',
            'symptoms.*.duration' => 'nullable|integer|min:0',
        ]);

        // Create the diagnosis
        $diagnosis = PatientDiagnosis::create([
            'appointment_id' => $validated['appointment_id'],
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $doctor->doctor_id,
            'diagnosis_code_id' => $validated['diagnosis_code_id'],
            'diagnosis_type' => $validated['diagnosis_type'],
            'certainty' => $validated['certainty'],
            'diagnosis_date' => $validated['diagnosis_date'],
            'clinical_notes' => $validated['clinical_notes'] ?? null,
            'treatment_plan' => $validated['treatment_plan'] ?? null,
            'status' => $validated['status'],
            'requires_referral' => $validated['requires_referral'] ?? false,
            'referral_to' => $validated['referral_to'] ?? null,
        ]);

        // Add symptoms if provided
        if ($request->has('symptoms')) {
            foreach ($request->symptoms as $symptom) {
                if (!empty($symptom['name'])) {
                    DiagnosisSymptom::create([
                        'patient_diagnosis_id' => $diagnosis->patient_diagnosis_id,
                        'symptom_name' => $symptom['name'],
                        'severity' => $symptom['severity'],
                        'duration_days' => $symptom['duration'] ?? null,
                    ]);
                }
            }
        }

        // Log this in the appointment workflow if needed
        $appointment = Appointment::find($validated['appointment_id']);
        if ($appointment) {
            $diagnosisCode = DiagnosisCode::find($validated['diagnosis_code_id']);
            $appointment->logWorkflowChange(
                $appointment->status,
                $appointment->status, // Status doesn't change
                auth()->id(),
                'doctor',
                "Diagnosis recorded: {$diagnosisCode->diagnosis_name} ({$diagnosisCode->icd10_code})"
            );
        }

        return redirect()
            ->back()
            ->with('success', 'Diagnosis recorded successfully');
    }

    /**
     * Get diagnoses for a patient
     */
    public function getPatientDiagnoses($patientId)
    {
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();

        $diagnoses = PatientDiagnosis::where('patient_id', $patientId)
            ->where('doctor_id', $doctor->doctor_id)
            ->with(['diagnosisCode', 'symptoms'])
            ->orderBy('diagnosis_date', 'desc')
            ->get();

        return response()->json($diagnoses);
    }

    /**
     * Update diagnosis status (e.g., mark as resolved)
     */
    public function updateStatus(Request $request, $id)
    {
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();

        $diagnosis = PatientDiagnosis::where('patient_diagnosis_id', $id)
            ->where('doctor_id', $doctor->doctor_id)
            ->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|in:Active,Resolved,Chronic,Under Treatment',
            'resolved_date' => 'nullable|required_if:status,Resolved|date',
        ]);

        $diagnosis->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Diagnosis status updated successfully'
        ]);
    }
}
