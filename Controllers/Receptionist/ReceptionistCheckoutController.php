<?php
// app/Http/Controllers/Receptionist/ReceptionistCheckOutController.php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BillingItem;
use App\Models\PrescriptionDispensing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReceptionistCheckOutController extends Controller
{
    /**
     * Show checkout page
     */
    public function index(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));

        // ✅ FIX: Only show appointments where:
        // 1. Status is COMPLETED
        // 2. Either NO prescriptions OR all prescriptions are DISPENSED
        $pendingCheckouts = Appointment::with([
            'patient.user',
            'doctor.user',
            'billingItems',
            'prescriptions.dispensing'
        ])
            ->whereDate('appointment_date', $date)
            ->where('status', Appointment::STATUS_COMPLETED)
            ->whereNull('checked_out_at')
            ->get()
            ->filter(function ($appointment) {
                // If no prescriptions, ready for checkout
                if ($appointment->prescriptions->isEmpty()) {
                    return true;
                }

                // If has prescriptions, ALL must be dispensed
                $allDispensed = $appointment->prescriptions->every(function ($prescription) {
                    return $prescription->dispensing &&
                        $prescription->dispensing->verification_status === 'Dispensed';
                });

                return $allDispensed;
            })
            ->sortByDesc('consultation_ended_at');

        $checkedOutToday = Appointment::with(['patient.user', 'doctor.user', 'checkedOutBy'])
            ->whereDate('checked_out_at', today())
            ->orderBy('checked_out_at', 'desc')
            ->take(20)
            ->get();

        $stats = [
            'pending_checkout' => $pendingCheckouts->count(),
            'checked_out_today' => Appointment::whereDate('checked_out_at', today())->count(),
            'total_collected_today' => Appointment::whereDate('checked_out_at', today())
                ->where('payment_collected', true)
                ->sum('total_amount'),
        ];

        return view('receptionist.receptionist_checkout', compact(
            'pendingCheckouts',
            'checkedOutToday',
            'stats',
            'date'
        ));
    }

    /**
     * Show checkout form with complete billing breakdown
     */
    public function show($appointmentId)
    {
        $appointment = Appointment::with([
            'patient.user',
            'doctor.user',
            'billingItems.addedBy',
            'prescriptions.items',
            'prescriptions.dispensing'
        ])->findOrFail($appointmentId);

        if ($appointment->status !== Appointment::STATUS_COMPLETED) {
            return redirect()->back()->with('error', 'Appointment is not completed yet.');
        }

        // ✅ FIX: Check if prescriptions are dispensed before allowing checkout
        if ($appointment->prescriptions->isNotEmpty()) {
            $hasPendingPrescriptions = $appointment->prescriptions->contains(function ($prescription) {
                return !$prescription->dispensing ||
                    $prescription->dispensing->verification_status !== 'Dispensed';
            });

            if ($hasPendingPrescriptions) {
                return redirect()->back()->with(
                    'error',
                    '⚠️ Cannot process payment: Patient has pending prescriptions that must be dispensed by pharmacist first. ' .
                        'Please direct patient to pharmacy counter.'
                );
            }
        }

        // ========================================
        // 1. CONSULTATION FEE
        // ========================================
        $consultationFee = $this->calculateConsultationFee($appointment->doctor);

        // ========================================
        // 2. PROCEDURES & TESTS (added by doctor)
        // ========================================
        $procedures = $appointment->billingItems()
            ->whereIn('item_type', ['procedure', 'lab_test', 'imaging', 'diagnostic_test'])
            ->get();
        $proceduresFee = $procedures->sum('amount');

        // ========================================
        // 3. PHARMACY FEES (from dispensed prescriptions)
        // ========================================
        $pharmacyFee = 0;
        $medications = collect();

        if ($appointment->prescriptions->isNotEmpty()) {
            foreach ($appointment->prescriptions as $prescription) {
                $dispensing = $prescription->dispensing;

                // ✅ Only include DISPENSED medications
                if ($dispensing && $dispensing->verification_status === 'Dispensed') {
                    foreach ($prescription->items as $item) {
                        // Calculate medication price
                        $price = $item->unit_price ?? 0;

                        $medications->push([
                            'name' => $item->medicine_name,
                            'dosage' => $item->dosage,
                            'frequency' => $item->frequency,
                            'quantity' => $item->quantity ?? 1,
                            'unit_price' => $price,
                            'total_price' => $item->total_price ?? $price,
                        ]);

                        $pharmacyFee += $item->total_price ?? $price;
                    }
                }
            }
        }

        // ========================================
        // 4. CALCULATE TOTALS
        // ========================================
        $subtotal = $consultationFee + $proceduresFee + $pharmacyFee;

        // Malaysian SST: 6% for private healthcare
        $taxRate = 0.06;
        $tax = $subtotal * $taxRate;

        $total = $subtotal + $tax;

        return view('receptionist.receptionist_checkoutForm', compact(
            'appointment',
            'consultationFee',
            'procedures',
            'proceduresFee',
            'medications',
            'pharmacyFee',
            'subtotal',
            'tax',
            'taxRate',
            'total'
        ));
    }

    /**
     * Process checkout and payment
     */
    public function processCheckout(Request $request, $appointmentId)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,card,insurance,online',
            'amount_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|required_with:discount_amount',
        ]);

        $appointment = Appointment::with(['billingItems', 'prescriptions.items', 'prescriptions.dispensing'])->findOrFail($appointmentId);

        if ($appointment->status !== Appointment::STATUS_COMPLETED) {
            return redirect()->back()->with('error', 'Cannot checkout: Appointment not completed.');
        }

        if ($appointment->checked_out_at) {
            return redirect()->back()->with('error', 'Appointment already checked out.');
        }

        // ✅ FIX: Final validation - ensure all prescriptions are dispensed
        if ($appointment->prescriptions->isNotEmpty()) {
            $hasPendingPrescriptions = $appointment->prescriptions->contains(function ($prescription) {
                return !$prescription->dispensing ||
                    $prescription->dispensing->verification_status !== 'Dispensed';
            });

            if ($hasPendingPrescriptions) {
                return redirect()->back()->with(
                    'error',
                    '⚠️ Cannot process payment: Patient has pending prescriptions. ' .
                        'All medications must be dispensed before checkout.'
                );
            }
        }

        DB::beginTransaction();

        try {
            // ========================================
            // RECALCULATE TOTALS
            // ========================================

            // 1. Consultation Fee
            $consultationFee = $this->calculateConsultationFee($appointment->doctor);

            // 2. Procedures & Tests
            $proceduresFee = $appointment->billingItems()
                ->whereIn('item_type', ['procedure', 'lab_test', 'imaging', 'diagnostic_test'])
                ->sum('amount');

            // 3. Pharmacy (only dispensed items)
            $pharmacyFee = 0;
            if ($appointment->prescriptions->isNotEmpty()) {
                foreach ($appointment->prescriptions as $prescription) {
                    if ($prescription->dispensing && $prescription->dispensing->verification_status === 'Dispensed') {
                        foreach ($prescription->items as $item) {
                            $pharmacyFee += $item->total_price ?? $item->unit_price ?? 0;
                        }
                    }
                }
            }

            // Calculate totals
            $subtotal = $consultationFee + $proceduresFee + $pharmacyFee;
            $discountAmount = $validated['discount_amount'] ?? 0;
            $taxableAmount = $subtotal - $discountAmount;
            $tax = $taxableAmount * 0.06; // 6% SST
            $total = $taxableAmount + $tax;

            // ========================================
            // UPDATE APPOINTMENT
            // ========================================
            $appointment->update([
                'consultation_fee' => $consultationFee,
                'procedures_fee' => $proceduresFee,
                'pharmacy_fee' => $pharmacyFee,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'payment_method' => $validated['payment_method'],
                'payment_amount' => $validated['amount_paid'],
                'payment_collected' => true,
                'checked_out_at' => now(),
                'checked_out_by' => Auth::id(),
                'billing_notes' => $validated['notes'] ?? null,
                'checkout_notes' => $validated['discount_reason'] ?? null,
            ]);

            // Update patient's last visit
            $appointment->patient->update([
                'last_visit_date' => now()
            ]);

            // Mark pharmacy as paid
            if ($appointment->prescriptions->isNotEmpty()) {
                foreach ($appointment->prescriptions as $prescription) {
                    if ($prescription->dispensing) {
                        $prescription->dispensing->update([
                            'payment_status' => 'Paid',
                            'payment_date' => now(),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('receptionist.checkout.receipt', $appointment->appointment_id)
                ->with('success', 'Checkout completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkout error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Checkout failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display printable receipt
     */
    public function receipt($appointmentId)
    {
        $appointment = Appointment::with([
            'patient.user',
            'doctor.user',
            'billingItems.addedBy',
            'prescriptions.items',
            'prescriptions.dispensing',
            'checkedOutBy'
        ])->findOrFail($appointmentId);

        if (!$appointment->checked_out_at) {
            return redirect()->back()->with('error', 'Appointment has not been checked out yet.');
        }

        // Get itemized breakdown
        $procedures = $appointment->billingItems()
            ->whereIn('item_type', ['procedure', 'lab_test', 'imaging', 'diagnostic_test'])
            ->get();

        $medications = collect();
        if ($appointment->prescriptions->isNotEmpty()) {
            foreach ($appointment->prescriptions as $prescription) {
                if ($prescription->dispensing && $prescription->dispensing->verification_status === 'Dispensed') {
                    foreach ($prescription->items as $item) {
                        $medications->push($item);
                    }
                }
            }
        }

        // ✅ FIX: Retrieve saved financial data from the appointment record
        $consultationFee = $appointment->consultation_fee ?? 0;
        $pharmacyFee = $appointment->pharmacy_fee ?? 0;
        $subtotal = $appointment->subtotal ?? 0;
        $tax = $appointment->tax_amount ?? 0; // View expects $tax, DB has tax_amount
        $total = $appointment->total_amount ?? 0;

        return view('receptionist.receptionist_receipt', compact(
            'appointment',
            'procedures',
            'medications',
            // ✅ Pass these variables to the view
            'consultationFee',
            'pharmacyFee',
            'subtotal',
            'tax',
            'total'
        ));
    }

    /**
     * Calculate consultation fee based on doctor specialization
     */
    private function calculateConsultationFee($doctor): float
    {
        $fees = [
            'General Medicine' => 50.00,
            'Pediatrics' => 60.00,
            'Cardiology' => 80.00,
            'Orthopedics' => 75.00,
            'Dermatology' => 65.00,
            'Psychiatry' => 90.00,
            'Neurology' => 85.00,
            'Gastroenterology' => 75.00,
            'Endocrinology' => 70.00,
            'default' => 50.00,
        ];

        return $fees[$doctor->specialization] ?? $fees['default'];
    }
}
