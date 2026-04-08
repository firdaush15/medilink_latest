<?php
// app/Http/Controllers/Admin/AdminDiagnosisController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DiagnosisCode;
use Illuminate\Support\Facades\DB;

class AdminDiagnosisController extends Controller
{
    /**
     * Display diagnosis management page
     */
    public function index(Request $request)
    {
        $query = DiagnosisCode::query();

        // Apply filters
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $diagnoses = $query->orderBy('diagnosis_name')->get();

        // Statistics
        $totalDiagnoses = DiagnosisCode::count();
        $activeDiagnoses = DiagnosisCode::where('is_active', true)->count();
        $infectiousDiagnoses = DiagnosisCode::where('is_infectious', true)->count();
        $chronicDiagnoses = DiagnosisCode::where('is_chronic', true)->count();

        return view('admin.admin_diagnosisManagement', compact(
            'diagnoses',
            'totalDiagnoses',
            'activeDiagnoses',
            'infectiousDiagnoses',
            'chronicDiagnoses'
        ));
    }

    /**
     * Store new diagnosis
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'icd10_code' => 'required|string|max:10|unique:diagnosis_codes,icd10_code',
            'diagnosis_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'severity' => 'required|in:Minor,Moderate,Severe,Critical',
            'is_chronic' => 'nullable|boolean',
            'is_infectious' => 'nullable|boolean',
            'requires_followup' => 'nullable|boolean',
            'typical_recovery_days' => 'nullable|integer|min:0',
        ]);

        // Convert checkbox values
        $validated['is_chronic'] = $request->has('is_chronic');
        $validated['is_infectious'] = $request->has('is_infectious');
        $validated['requires_followup'] = $request->has('requires_followup');
        $validated['is_active'] = true;

        DiagnosisCode::create($validated);

        return redirect()
            ->route('admin.diagnoses.index')
            ->with('success', 'Diagnosis added successfully! Doctors can now use this diagnosis.');
    }

    /**
     * Get diagnosis for editing
     */
    public function edit($id)
    {
        $diagnosis = DiagnosisCode::findOrFail($id);
        return response()->json($diagnosis);
    }

    /**
     * Update existing diagnosis
     */
    public function update(Request $request, $id)
    {
        $diagnosis = DiagnosisCode::findOrFail($id);

        $validated = $request->validate([
            'icd10_code' => 'required|string|max:10|unique:diagnosis_codes,icd10_code,' . $id . ',diagnosis_code_id',
            'diagnosis_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'severity' => 'required|in:Minor,Moderate,Severe,Critical',
            'is_chronic' => 'nullable|boolean',
            'is_infectious' => 'nullable|boolean',
            'requires_followup' => 'nullable|boolean',
            'typical_recovery_days' => 'nullable|integer|min:0',
        ]);

        // Convert checkbox values
        $validated['is_chronic'] = $request->has('is_chronic');
        $validated['is_infectious'] = $request->has('is_infectious');
        $validated['requires_followup'] = $request->has('requires_followup');

        $diagnosis->update($validated);

        return redirect()
            ->route('admin.diagnoses.index')
            ->with('success', 'Diagnosis updated successfully!');
    }

    /**
     * Toggle diagnosis active status
     */
    public function toggleStatus(Request $request, $id)
    {
        $diagnosis = DiagnosisCode::findOrFail($id);
        
        $newStatus = $request->input('is_active', !$diagnosis->is_active);
        $diagnosis->update(['is_active' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => $newStatus 
                ? 'Diagnosis activated. Doctors can now use this diagnosis.' 
                : 'Diagnosis deactivated. This will not affect existing patient records.',
            'is_active' => $newStatus
        ]);
    }

    /**
     * Get diagnosis usage statistics
     */
    public function getUsageStats($id)
    {
        $diagnosis = DiagnosisCode::findOrFail($id);

        $stats = [
            'total_uses' => $diagnosis->patientDiagnoses()->count(),
            'active_cases' => $diagnosis->patientDiagnoses()->where('status', 'Active')->count(),
            'resolved_cases' => $diagnosis->patientDiagnoses()->where('status', 'Resolved')->count(),
            'last_used' => $diagnosis->patientDiagnoses()->latest('diagnosis_date')->first()?->diagnosis_date,
            'most_common_doctor' => DB::table('patient_diagnoses')
                ->join('doctors', 'patient_diagnoses.doctor_id', '=', 'doctors.doctor_id')
                ->join('users', 'doctors.user_id', '=', 'users.id')
                ->where('patient_diagnoses.diagnosis_code_id', $id)
                ->select('users.name', DB::raw('COUNT(*) as count'))
                ->groupBy('users.name')
                ->orderBy('count', 'desc')
                ->first(),
        ];

        return response()->json($stats);
    }

    /**
     * Bulk import diagnoses from CSV
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('csv_file');
        $csvData = array_map('str_getcsv', file($file));
        $header = array_shift($csvData);

        $imported = 0;
        $errors = [];

        foreach ($csvData as $row) {
            try {
                $data = array_combine($header, $row);
                
                DiagnosisCode::create([
                    'icd10_code' => $data['icd10_code'],
                    'diagnosis_name' => $data['diagnosis_name'],
                    'category' => $data['category'],
                    'severity' => $data['severity'] ?? 'Moderate',
                    'is_infectious' => filter_var($data['is_infectious'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'is_chronic' => filter_var($data['is_chronic'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'is_active' => true,
                ]);
                
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row error: {$e->getMessage()}";
            }
        }

        return redirect()
            ->route('admin.diagnoses.index')
            ->with('success', "Successfully imported {$imported} diagnoses." . 
                   (count($errors) > 0 ? " Errors: " . implode(', ', $errors) : ''));
    }

    /**
     * Export diagnoses to CSV
     */
    public function export()
    {
        $diagnoses = DiagnosisCode::all();

        $filename = 'diagnosis_codes_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($diagnoses) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'ICD-10 Code',
                'Diagnosis Name',
                'Category',
                'Severity',
                'Infectious',
                'Chronic',
                'Requires Follow-up',
                'Recovery Days',
                'Status'
            ]);

            // Data rows
            foreach ($diagnoses as $diagnosis) {
                fputcsv($file, [
                    $diagnosis->icd10_code,
                    $diagnosis->diagnosis_name,
                    $diagnosis->category,
                    $diagnosis->severity,
                    $diagnosis->is_infectious ? 'Yes' : 'No',
                    $diagnosis->is_chronic ? 'Yes' : 'No',
                    $diagnosis->requires_followup ? 'Yes' : 'No',
                    $diagnosis->typical_recovery_days,
                    $diagnosis->is_active ? 'Active' : 'Inactive'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}