<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\PrescriptionDispensing;
use App\Models\MedicineInventory;
use App\Models\PatientAllergy;
use App\Models\StaffAlert;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorPrescriptionController extends Controller
{
    /**
     * Store prescription with allergy checking + ✅ alert pharmacist
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id'              => 'required|exists:patients,patient_id',
            'doctor_id'               => 'required|exists:doctors,doctor_id',
            'appointment_id'          => 'required|exists:appointments,appointment_id',
            'prescribed_date'         => 'required|date',
            'notes'                   => 'nullable|string',
            'medicines'               => 'required|array|min:1',
            'medicines.*.medicine_id' => 'required|exists:medicine_inventory,medicine_id',
            'medicines.*.medicine_name'        => 'required|string',
            'medicines.*.dosage'               => 'required|string',
            'medicines.*.frequency'            => 'required|string',
            'medicines.*.quantity_prescribed'  => 'required|integer|min:1',
            'medicines.*.days_supply'          => 'nullable|integer|min:1',
            'medicines.*.unit_price'           => 'required|numeric|min:0',
            'allergy_override'         => 'nullable|boolean',
            'allergy_override_reason'  => 'nullable|required_if:allergy_override,true|string',
        ]);

        // Pre-validate medicines against allergies
        if (!$request->has('allergy_override')) {
            $allergyWarnings = $this->validateMedicinesAgainstAllergies(
                $validated['medicines'],
                $validated['patient_id']
            );

            if (!empty($allergyWarnings['critical'])) {
                return redirect()
                    ->back()
                    ->withErrors(['allergy_error' => 'CRITICAL ALLERGY DETECTED: Prescription Blocked'])
                    ->with('critical_allergies', $allergyWarnings['critical'])
                    ->withInput();
            }

            if (!empty($allergyWarnings['warnings'])) {
                return redirect()
                    ->back()
                    ->withErrors(['allergy_warning' => 'ALLERGY WARNING: Review required'])
                    ->with('allergy_warnings', $allergyWarnings['warnings'])
                    ->withInput();
            }
        }

        DB::beginTransaction();

        try {
            $prescriptionData = [
                'appointment_id' => $validated['appointment_id'],
                'doctor_id'      => $validated['doctor_id'],
                'patient_id'     => $validated['patient_id'],
                'prescribed_date'=> $validated['prescribed_date'],
                'notes'          => $validated['notes'] ?? null,
            ];

            if ($request->has('allergy_override') && $request->allergy_override) {
                $prescriptionData['notes'] = ($prescriptionData['notes'] ?? '') .
                    "\n\n⚠️ ALLERGY OVERRIDE: " . $validated['allergy_override_reason'];
            }

            $prescription = Prescription::create($prescriptionData);

            foreach ($validated['medicines'] as $medicine) {
                $unitPrice  = $medicine['unit_price'];
                $quantity   = $medicine['quantity_prescribed'];
                $totalPrice = $unitPrice * $quantity;
                $daysSupply = $medicine['days_supply'] ??
                    PrescriptionItem::calculateDaysSupply($quantity, $medicine['frequency']);

                PrescriptionItem::create([
                    'prescription_id'    => $prescription->prescription_id,
                    'medicine_id'        => $medicine['medicine_id'],
                    'medicine_name'      => $medicine['medicine_name'],
                    'dosage'             => $medicine['dosage'],
                    'frequency'          => $medicine['frequency'],
                    'quantity_prescribed'=> $quantity,
                    'days_supply'        => $daysSupply,
                    'unit_price'         => $unitPrice,
                    'total_price'        => $totalPrice,
                    'quantity_dispensed' => 0,
                ]);
            }

            // Create dispensing record for pharmacist
            $dispensing = PrescriptionDispensing::create([
                'prescription_id'      => $prescription->prescription_id,
                'patient_id'           => $validated['patient_id'],
                'verification_status'  => 'Pending',
                'payment_status'       => 'Pending',
                'special_instructions' => $request->has('allergy_override') && $request->allergy_override
                    ? '⚠️ ALLERGY OVERRIDE BY DOCTOR - Verify with prescribing physician before dispensing'
                    : null,
            ]);

            DB::commit();

            // ✅ Alert ALL pharmacists about the new prescription (after commit)
            $this->alertPharmacistsAboutPrescription($prescription, $dispensing, $request->has('allergy_override') && $request->allergy_override);

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
     * ✅ NEW: Alert all available pharmacists about a new prescription
     */
    protected function alertPharmacistsAboutPrescription($prescription, $dispensing, bool $hasAllergyOverride = false)
    {
        $pharmacists = User::where('role', 'pharmacist')->get();

        if ($pharmacists->isEmpty()) {
            return;
        }

        $priority     = $hasAllergyOverride ? 'High' : 'Normal';
        $allergyNote  = $hasAllergyOverride ? ' ⚠️ Contains allergy override — verify with prescribing doctor.' : '';
        $medicineCount = $prescription->items()->count();

        foreach ($pharmacists as $pharmacist) {
            StaffAlert::create([
                'sender_id'       => auth()->id(),
                'sender_type'     => 'doctor',
                'recipient_id'    => $pharmacist->id,
                'recipient_type'  => 'pharmacist',
                'patient_id'      => $prescription->patient_id,
                'prescription_id' => $prescription->prescription_id,
                'alert_type'      => 'New Prescription',
                'priority'        => $priority,
                'alert_title'     => "💊 New Prescription — {$medicineCount} medication(s)",
                'alert_message'   => "Dr. " . auth()->user()->name . " has issued a prescription for patient " .
                                     ($prescription->patient->user->name ?? 'N/A') .
                                     ". Please verify and dispense." . $allergyNote,
                'action_url'      => route('pharmacist.prescriptions.show', $prescription->prescription_id),
            ]);
        }
    }

    /**
     * Validate all medicines in prescription against patient allergies
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
        $regularWarnings  = [];

        foreach ($medicines as $medicine) {
            $medicineRecord = MedicineInventory::find($medicine['medicine_id']);

            foreach ($drugAllergies as $allergy) {
                $allergenName = strtolower($allergy->allergen_name);
                $medicineName = strtolower($medicineRecord->medicine_name);
                $genericName  = strtolower($medicineRecord->generic_name ?? '');
                $brandName    = strtolower($medicineRecord->brand_name ?? '');

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

                    if (in_array($allergy->severity, ['Life-threatening', 'Severe'])) {
                        $criticalWarnings[] = $warningData;
                    } else {
                        $regularWarnings[] = $warningData;
                    }
                }
            }
        }

        return ['critical' => $criticalWarnings, 'warnings' => $regularWarnings];
    }

    /**
     * Get allergy-safe alternative medications
     */
    public function getSafeAlternatives(Request $request)
    {
        $medicineId = $request->get('medicine_id');
        $patientId  = $request->get('patient_id');

        $currentMedicine = MedicineInventory::findOrFail($medicineId);

        $alternatives = MedicineInventory::where('status', 'Active')
            ->where('quantity_in_stock', '>', 0)
            ->where('category', $currentMedicine->category)
            ->where('medicine_id', '!=', $medicineId)
            ->get();

        $drugAllergies = PatientAllergy::where('patient_id', $patientId)
            ->where('allergy_type', 'Drug/Medication')
            ->where('is_active', true)
            ->get();

        $safeAlternatives = $alternatives->filter(function ($medicine) use ($drugAllergies) {
            foreach ($drugAllergies as $allergy) {
                $allergenName = strtolower($allergy->allergen_name);
                $medicineName = strtolower($medicine->medicine_name);
                $genericName  = strtolower($medicine->generic_name ?? '');

                if (
                    str_contains($medicineName, $allergenName) ||
                    str_contains($allergenName, $medicineName) ||
                    str_contains($genericName, $allergenName) ||
                    str_contains($allergenName, $genericName)
                ) {
                    return false;
                }
            }
            return true;
        });

        return response()->json([
            'success'            => true,
            'current_medicine'   => $currentMedicine->medicine_name,
            'category'           => $currentMedicine->category,
            'safe_alternatives'  => $safeAlternatives->values(),
            'has_safe_alternatives' => $safeAlternatives->isNotEmpty(),
        ]);
    }
}