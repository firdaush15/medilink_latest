<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MedicalRecord;
use App\Models\Doctor;
use Illuminate\Support\Facades\Storage;

class DoctorMedicalRecordController extends Controller
{
    public function store(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        if (!$doctor) {
            return redirect()->back()->with('error', 'Doctor profile not found');
        }

        // Validate the request
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,patient_id',
            'record_date' => 'required|date',
            'record_type' => 'required|string|max:255',
            'record_title' => 'required|string|max:255',
            'description' => 'required|string',
            'file_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // 10MB max
        ]);

        // Handle file upload if present
        $filePath = null;
        if ($request->hasFile('file_path')) {
            $file = $request->file('file_path');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('medical_records', $fileName, 'public');
        }

        // Create medical record
        $medicalRecord = MedicalRecord::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $doctor->doctor_id,
            'record_date' => $validated['record_date'],
            'record_type' => $validated['record_type'],
            'record_title' => $validated['record_title'],
            'description' => $validated['description'],
            'file_path' => $filePath,
        ]);

        return redirect()->back()->with('success', 'Medical record added successfully');
    }
}