<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\PrescriptionDispensing;
use App\Models\Prescription;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PharmacistPrescriptionController extends Controller
{
    /**
     * Display list of prescriptions with filters
     */
    public function index(Request $request)
    {
        $pharmacist = Auth::user()->pharmacist;
        $status = $request->get('status', 'pending');

        // Get all doctors for filter
        $doctors = Doctor::with('user')->get();

        // Base query
        $query = PrescriptionDispensing::with([
            'prescription.doctor.user',
            'prescription.items',
            'patient.user'
        ]);

        // Apply status filter
        switch ($status) {
            case 'pending':
                $query->where('verification_status', 'Pending');
                break;
            case 'verified':
                $query->where('verification_status', 'Verified');
                break;
            case 'dispensed':
                $query->where('verification_status', 'Dispensed');
                break;
            case 'rejected':
                $query->where('verification_status', 'Rejected');
                break;
        }

        // Apply date filter if provided
        if ($request->has('date')) {
            switch ($request->get('date')) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', today()->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month);
                    break;
            }
        }

        // Apply doctor filter if provided
        if ($request->has('doctor_id') && $request->doctor_id) {
            $query->whereHas('prescription', function ($q) use ($request) {
                $q->where('doctor_id', $request->doctor_id);
            });
        }

        // Get counts for tabs
        $pendingCount = PrescriptionDispensing::where('verification_status', 'Pending')->count();
        $verifiedCount = PrescriptionDispensing::where('verification_status', 'Verified')->count();
        $dispensedCount = PrescriptionDispensing::where('verification_status', 'Dispensed')->count();

        // Paginate results
        $prescriptions = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('pharmacist.pharmacist_prescriptions', compact(
            'prescriptions',
            'doctors',
            'pendingCount',
            'verifiedCount',
            'dispensedCount'
        ));
    }

    /**
     * Display prescription details
     */
    public function show($id)
    {
        $prescription = Prescription::with([
            'patient.user',
            'patient.activeAllergies',
            'doctor.user',
            'items',
            'appointment'
        ])->findOrFail($id);

        $dispensing = PrescriptionDispensing::where('prescription_id', $id)
            ->with('pharmacist.user')
            ->first();

        if (!$dispensing) {
            return redirect()->route('pharmacist.prescriptions')
                ->with('error', 'Dispensing record not found');
        }

        // Check for drug interactions with patient allergies
        $allergyWarnings = [];
        foreach ($prescription->items as $item) {
            $matchingAllergies = $prescription->patient->drugAllergies()
                ->where(function ($query) use ($item) {
                    $query->where('allergen_name', 'LIKE', "%{$item->medicine_name}%")
                        ->orWhereRaw('? LIKE CONCAT("%", allergen_name, "%")', [$item->medicine_name]);
                })
                ->get();

            if ($matchingAllergies->count() > 0) {
                foreach ($matchingAllergies as $allergy) {
                    $allergyWarnings[] = [
                        'medicine' => $item->medicine_name,
                        'allergen' => $allergy->allergen_name,
                        'severity' => $allergy->severity,
                        'reaction' => $allergy->reaction_description,
                    ];
                }
            }
        }

        return view('pharmacist.pharmacist_prescriptionDetails', compact(
            'prescription',
            'dispensing',
            'allergyWarnings'
        ));
    }

    /**
     * Verify prescription
     */
    public function verify(Request $request, $id)
    {
        try {
            // Get dispensing record
            $dispensing = PrescriptionDispensing::where('prescription_id', $id)->firstOrFail();

            // Check if already verified
            if ($dispensing->verification_status !== 'Pending') {
                return redirect()->back()
                    ->with('error', 'This prescription has already been ' . strtolower($dispensing->verification_status));
            }

            // Get prescription with allergy data
            $prescription = Prescription::with(['patient.activeAllergies', 'items'])->findOrFail($id);

            // Check for drug interactions with patient allergies
            $hasAllergyWarnings = false;
            foreach ($prescription->items as $item) {
                $matchingAllergies = $prescription->patient->drugAllergies()
                    ->where(function ($query) use ($item) {
                        $query->where('allergen_name', 'LIKE', "%{$item->medicine_name}%")
                            ->orWhereRaw('? LIKE CONCAT("%", allergen_name, "%")', [$item->medicine_name]);
                    })
                    ->exists();

                if ($matchingAllergies) {
                    $hasAllergyWarnings = true;
                    break;
                }
            }

            // Build validation rules dynamically
            $rules = [
                'allergy_checked' => 'required|accepted',
                'interaction_checked' => 'required|accepted',
                'verification_notes' => 'nullable|string',
            ];

            // Only require doctor_contacted if there ARE allergy warnings
            if ($hasAllergyWarnings) {
                $rules['doctor_contacted'] = 'required|accepted';
            }

            $validated = $request->validate($rules);

            $dispensing->update([
                'verification_status' => 'Verified',
                'pharmacist_id' => auth()->user()->pharmacist->pharmacist_id,
                'allergy_checked' => true,
                'interaction_checked' => true,
                'verification_notes' => $validated['verification_notes'] ?? null,
                'verified_at' => now(),
            ]);

            return redirect()->route('pharmacist.prescriptions', ['status' => 'verified'])
                ->with('success', 'Prescription verified successfully and ready for dispensing');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to verify prescription: ' . $e->getMessage());
        }
    }

    /**
     * Reject prescription
     */
    public function reject(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'rejection_reason' => 'required|string|min:10',
            ]);

            $dispensing = PrescriptionDispensing::where('prescription_id', $id)->firstOrFail();

            // Check if already processed
            if ($dispensing->verification_status !== 'Pending') {
                return redirect()->back()
                    ->with('error', 'This prescription has already been ' . strtolower($dispensing->verification_status));
            }

            $dispensing->update([
                'verification_status' => 'Rejected',
                'pharmacist_id' => auth()->user()->pharmacist->pharmacist_id,
                'verification_notes' => $validated['rejection_reason'],
                'verified_at' => now(),
            ]);

            return redirect()->route('pharmacist.prescriptions', ['status' => 'rejected'])
                ->with('success', 'Prescription rejected');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to reject prescription: ' . $e->getMessage());
        }
    }

    /**
     * Dispense prescription
     */
    public function dispense(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'patient_counseled' => 'required|accepted',
                'counseling_notes' => 'required|string|min:20',
                'special_instructions' => 'nullable|string',
            ]);

            $dispensing = PrescriptionDispensing::where('prescription_id', $id)->firstOrFail();

            if ($dispensing->verification_status !== 'Verified') {
                return response()->json([
                    'success' => false,
                    'message' => 'Prescription must be verified first'
                ], 400);
            }

            if (!auth()->user()->pharmacist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pharmacist record not found'
                ], 400);
            }

            DB::beginTransaction();

            try {
                $prescription = Prescription::with('items.medicine')->findOrFail($id);
                $totalAmount = 0;

                // ========================================
                // DISPENSE EACH MEDICATION & UPDATE STOCK
                // ========================================
                foreach ($prescription->items as $item) {
                    if (!$item->medicine_id) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Medicine '{$item->medicine_name}' not linked to inventory"
                        ], 400);
                    }

                    $medicine = $item->medicine;

                    // Check stock availability
                    if ($medicine->quantity_in_stock < $item->quantity_prescribed) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Insufficient stock for {$medicine->medicine_name}. Available: {$medicine->quantity_in_stock}, Required: {$item->quantity_prescribed}"
                        ], 400);
                    }

                    // Reduce stock
                    $medicine->reduceStock(
                        $item->quantity_prescribed,
                        auth()->user()->pharmacist->pharmacist_id,
                        "Dispensed for prescription #{$prescription->prescription_id}"
                    );

                    // Update prescription item with dispensing details
                    $item->update([
                        'quantity_dispensed' => $item->quantity_prescribed,
                        'batch_number' => $medicine->batch_number,
                        'expiry_date' => $medicine->expiry_date,
                    ]);

                    $totalAmount += $item->total_price;
                }

                // ========================================
                // UPDATE DISPENSING RECORD
                // ========================================
                $dispensing->update([
                    'verification_status' => 'Dispensed',
                    'dispensed_at' => now(),
                    'patient_counseled' => true,
                    'counseling_notes' => $validated['counseling_notes'],
                    'special_instructions' => $validated['special_instructions'] ?? null,
                    'pharmacist_id' => auth()->user()->pharmacist->pharmacist_id,
                    'total_amount' => $totalAmount,
                    'payment_status' => 'Pending', // Payment handled by receptionist
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Prescription dispensed successfully. Total: RM ' . number_format($totalAmount, 2) . '. Patient should proceed to receptionist for checkout.',
                    'redirect' => route('pharmacist.prescriptions', ['status' => 'dispensed'])
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Prescription not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Dispense error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Failed to dispense prescription: ' . $e->getMessage()
            ], 500);
        }
    }
}
