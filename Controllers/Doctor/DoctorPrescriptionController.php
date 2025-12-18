<?php
// app/Http/Controllers/Doctor/DoctorPrescriptionController.php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\PrescriptionDispensing;
use App\Models\MedicineInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorPrescriptionController extends Controller
{
    /**
     * Store prescription with quantities and pricing
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
        ]);

        DB::beginTransaction();

        try {
            // Create prescription
            $prescription = Prescription::create([
                'appointment_id' => $validated['appointment_id'],
                'doctor_id' => $validated['doctor_id'],
                'patient_id' => $validated['patient_id'],
                'prescribed_date' => $validated['prescribed_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

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
                    'quantity_dispensed' => 0, // Not yet dispensed
                ]);
            }

            // Create dispensing record for pharmacist
            PrescriptionDispensing::create([
                'prescription_id' => $prescription->prescription_id,
                'patient_id' => $validated['patient_id'],
                'verification_status' => 'Pending',
                'payment_status' => 'Pending',
            ]);

            DB::commit();

            return redirect()
                ->route('doctor.patients.show', $validated['appointment_id'])
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
}