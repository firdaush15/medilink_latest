<?php
// app/Http/Controllers/Doctor/DoctorPrescriptionController.php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\PrescriptionDispensing;
use App\Models\MedicineInventory;
use App\Models\PatientAllergy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorPrescriptionController extends Controller
{
    /**
     * ✅ ENHANCED: Store prescription with allergy checking
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,patient_id',
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'appointment_id' => 'required|exists:appointments,appointment_id',
            'prescribed_date' => 'required|date',
            'notes' => 'nullable|string',
            'medicines' => 'required|array|min:1',
            'medicines.*.medicine_id' => 'required|exists:medicine_inventory,medicine_id',
            'medicines.*.medicine_name' => 'required|string',
            'medicines.*.dosage' => 'required|string',
            'medicines.*.frequency' => 'required|string',
            'medicines.*.quantity_prescribed' => 'required|integer|min:1',
            'medicines.*.days_supply' => 'nullable|integer|min:1',
            'medicines.*.unit_price' => 'required|numeric|min:0',
            'allergy_override' => 'nullable|boolean', // ✅ NEW: Allow override in special cases
            'allergy_override_reason' => 'nullable|required_if:allergy_override,true|string', // ✅ NEW: Require reason for override
        ]);

        // ✅ NEW: Pre-validate all medicines for allergies (unless override is requested)
        if (!$request->has('allergy_override')) {
            $allergyWarnings = $this->validateMedicinesAgainstAllergies(
                $validated['medicines'],
                $validated['patient_id']
            );

            if (!empty($allergyWarnings['critical'])) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'allergy_error' => 'CRITICAL ALLERGY DETECTED: Cannot prescribe the following medications:',
                        'critical_allergies' => $allergyWarnings['critical'],
                    ])
                    ->withInput();
            }

            if (!empty($allergyWarnings['warnings'])) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'allergy_warning' => 'ALLERGY WARNING: The following medications may cause allergic reactions:',
                        'allergy_warnings' => $allergyWarnings['warnings'],
                    ])
                    ->withInput();
            }
        }

        DB::beginTransaction();

        try {
            // Create prescription
            $prescriptionData = [
                'appointment_id' => $validated['appointment_id'],
                'doctor_id' => $validated['doctor_id'],
                'patient_id' => $validated['patient_id'],
                'prescribed_date' => $validated['prescribed_date'],
                'notes' => $validated['notes'] ?? null,
            ];

            // ✅ NEW: Add allergy override note if present
            if ($request->has('allergy_override') && $request->allergy_override) {
                $prescriptionData['notes'] = ($prescriptionData['notes'] ?? '') . 
                    "\n\n⚠️ ALLERGY OVERRIDE: " . $validated['allergy_override_reason'];
            }

            $prescription = Prescription::create($prescriptionData);

            // Create prescription items with quantities and pricing
            foreach ($validated['medicines'] as $medicine) {
                $medicineRecord = MedicineInventory::find($medicine['medicine_id']);
                
                // Calculate total price
                $unitPrice = $medicine['unit_price'];
                $quantity = $medicine['quantity_prescribed'];
                $totalPrice = $unitPrice * $quantity;
                
                // Auto-calculate days supply if not provided
                $daysSupply = $medicine['days_supply'] ?? 
                    PrescriptionItem::calculateDaysSupply($quantity, $medicine['frequency']);
                
                PrescriptionItem::create([
                    'prescription_id' => $prescription->prescription_id,
                    'medicine_id' => $medicine['medicine_id'],
                    'medicine_name' => $medicine['medicine_name'],
                    'dosage' => $medicine['dosage'],
                    'frequency' => $medicine['frequency'],
                    'quantity_prescribed' => $quantity,
                    'days_supply' => $daysSupply,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'quantity_dispensed' => 0,
                ]);
            }

            // Create dispensing record for pharmacist
            PrescriptionDispensing::create([
                'prescription_id' => $prescription->prescription_id,
                'patient_id' => $validated['patient_id'],
                'verification_status' => 'Pending',
                'payment_status' => 'Pending',
                // ✅ NEW: Flag for pharmacist if allergy override was used
                'special_instructions' => $request->has('allergy_override') && $request->allergy_override
                    ? '⚠️ ALLERGY OVERRIDE BY DOCTOR - Verify with prescribing physician before dispensing'
                    : null,
            ]);

            DB::commit();

            return redirect()
                ->route('doctor.appointments.update-patient', ['id' => $validated['appointment_id']])
                ->with('success', 'Prescription created successfully with ' . count($validated['medicines']) . ' medication(s).');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Prescription creation error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Failed to create prescription. Please try again.')
                ->withInput();
        }
    }

    /**
     * ✅ NEW: Validate all medicines in prescription against patient allergies
     */
    private function validateMedicinesAgainstAllergies($medicines, $patientId)
    {
        $drugAllergies = PatientAllergy::where('patient_id', $patientId)
            ->where('allergy_type', 'Drug/Medication')
            ->where('is_active', true)
            ->get();

        if ($drugAllergies->isEmpty()) {
            return ['critical' => [], 'warnings' => []];
        }

        $criticalWarnings = [];
        $regularWarnings = [];

        foreach ($medicines as $medicine) {
            $medicineRecord = MedicineInventory::find($medicine['medicine_id']);
            
            foreach ($drugAllergies as $allergy) {
                $allergenName = strtolower($allergy->allergen_name);
                $medicineName = strtolower($medicineRecord->medicine_name);
                $genericName = strtolower($medicineRecord->generic_name ?? '');
                $brandName = strtolower($medicineRecord->brand_name ?? '');

                // Check for matches
                if (
                    str_contains($medicineName, $allergenName) ||
                    str_contains($allergenName, $medicineName) ||
                    str_contains($genericName, $allergenName) ||
                    str_contains($allergenName, $genericName) ||
                    str_contains($brandName, $allergenName) ||
                    str_contains($allergenName, $brandName)
                ) {
                    $warningData = [
                        'medicine' => $medicineRecord->medicine_name,
                        'allergen' => $allergy->allergen_name,
                        'severity' => $allergy->severity,
                        'reaction' => $allergy->reaction_description,
                    ];

                    // Life-threatening and Severe = CRITICAL (Cannot prescribe)
                    if (in_array($allergy->severity, ['Life-threatening', 'Severe'])) {
                        $criticalWarnings[] = $warningData;
                    } else {
                        // Moderate and Mild = WARNING (Can prescribe with caution)
                        $regularWarnings[] = $warningData;
                    }
                }
            }
        }

        return [
            'critical' => $criticalWarnings,
            'warnings' => $regularWarnings,
        ];
    }

    /**
     * ✅ NEW: Get allergy-safe alternative medications
     */
    public function getSafeAlternatives(Request $request)
    {
        $medicineId = $request->get('medicine_id');
        $patientId = $request->get('patient_id');

        $currentMedicine = MedicineInventory::findOrFail($medicineId);

        // Find alternatives in the same category
        $alternatives = MedicineInventory::where('status', 'Active')
            ->where('quantity_in_stock', '>', 0)
            ->where('category', $currentMedicine->category)
            ->where('medicine_id', '!=', $medicineId)
            ->get();

        $drugAllergies = PatientAllergy::where('patient_id', $patientId)
            ->where('allergy_type', 'Drug/Medication')
            ->where('is_active', true)
            ->get();

        // Filter out medicines patient is allergic to
        $safeAlternatives = $alternatives->filter(function($medicine) use ($drugAllergies) {
            foreach ($drugAllergies as $allergy) {
                $allergenName = strtolower($allergy->allergen_name);
                $medicineName = strtolower($medicine->medicine_name);
                $genericName = strtolower($medicine->generic_name ?? '');

                if (
                    str_contains($medicineName, $allergenName) ||
                    str_contains($allergenName, $medicineName) ||
                    str_contains($genericName, $allergenName) ||
                    str_contains($allergenName, $genericName)
                ) {
                    return false; // This medicine is NOT safe
                }
            }
            return true; // This medicine is safe
        });

        return response()->json([
            'success' => true,
            'current_medicine' => $currentMedicine->medicine_name,
            'category' => $currentMedicine->category,
            'safe_alternatives' => $safeAlternatives->values(),
            'has_safe_alternatives' => $safeAlternatives->isNotEmpty(),
        ]);
    }
}