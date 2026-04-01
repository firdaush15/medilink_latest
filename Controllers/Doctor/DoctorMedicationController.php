<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\MedicineInventory;
use App\Models\Patient;
use App\Models\PatientAllergy;
use Illuminate\Http\Request;

class DoctorMedicationController extends Controller
{
    /**
     * Display available medications (Read-Only)
     * Doctors can view medications to check availability when prescribing
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');

        // Only show active medications with stock
        $query = MedicineInventory::where('status', 'Active')
            ->where('quantity_in_stock', '>', 0);

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('medicine_name', 'LIKE', "%{$search}%")
                  ->orWhere('generic_name', 'LIKE', "%{$search}%")
                  ->orWhere('brand_name', 'LIKE', "%{$search}%");
            });
        }

        // Apply category filter
        if ($category) {
            $query->where('category', $category);
        }

        // Paginate results
        $medicines = $query->orderBy('medicine_name')->paginate(20);

        // Get unique categories for filter
        $categories = MedicineInventory::where('status', 'Active')
            ->distinct()
            ->pluck('category')
            ->sort();

        return view('doctor.medications.index', compact(
            'medicines',
            'categories',
            'search',
            'category'
        ));
    }

    /**
     * Show medicine details
     */
    public function show($id)
    {
        $medicine = MedicineInventory::findOrFail($id);

        return view('doctor.medications.show', compact('medicine'));
    }

    /**
     * ✅ ENHANCED: AJAX search for medications with allergy check
     * Returns JSON data for autocomplete with allergy warnings
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        $patientId = $request->get('patient_id'); // ✅ NEW: Get patient ID for allergy check
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }
        
        $medicines = MedicineInventory::where('status', 'Active')
            ->where('quantity_in_stock', '>', 0)
            ->where(function($query) use ($search) {
                $query->where('medicine_name', 'LIKE', "%{$search}%")
                      ->orWhere('generic_name', 'LIKE', "%{$search}%")
                      ->orWhere('brand_name', 'LIKE', "%{$search}%");
            })
            ->take(10)
            ->get([
                'medicine_id',
                'medicine_name',
                'generic_name',
                'brand_name',
                'strength',
                'form',
                'quantity_in_stock',
                'unit_price'
            ]);

        // ✅ NEW: Check allergies for each medicine if patient_id is provided
        if ($patientId) {
            $medicines = $medicines->map(function($medicine) use ($patientId) {
                $allergyCheck = $this->checkMedicineAllergy($medicine, $patientId);
                
                $medicine->has_allergy = $allergyCheck['has_allergy'];
                $medicine->allergy_severity = $allergyCheck['severity'];
                $medicine->allergy_details = $allergyCheck['details'];
                $medicine->allergy_warning = $allergyCheck['warning_message'];
                
                return $medicine;
            });
        }

        return response()->json($medicines);
    }

    /**
     * ✅ NEW: Check medication availability with comprehensive allergy check
     * Returns stock status AND allergy warnings for a specific medicine
     */
    public function checkAvailability($id, Request $request)
    {
        $medicine = MedicineInventory::findOrFail($id);
        $patientId = $request->get('patient_id');

        $response = [
            'available' => $medicine->quantity_in_stock > 0,
            'quantity' => $medicine->quantity_in_stock,
            'status' => $medicine->status,
            'is_low_stock' => $medicine->isLowStock(),
            'unit_price' => $medicine->unit_price,
        ];

        // ✅ NEW: Add allergy check if patient_id is provided
        if ($patientId) {
            $allergyCheck = $this->checkMedicineAllergy($medicine, $patientId);
            $response = array_merge($response, $allergyCheck);
        }

        return response()->json($response);
    }

    /**
     * ✅ NEW: Comprehensive allergy checking method
     * Checks medicine against patient's known drug allergies
     */
    private function checkMedicineAllergy($medicine, $patientId)
    {
        // Get patient's active drug allergies
        $drugAllergies = PatientAllergy::where('patient_id', $patientId)
            ->where('allergy_type', 'Drug/Medication')
            ->where('is_active', true)
            ->get();

        if ($drugAllergies->isEmpty()) {
            return [
                'has_allergy' => false,
                'severity' => null,
                'details' => null,
                'warning_message' => null,
                'can_prescribe' => true,
            ];
        }

        // Check for direct matches or similar drug names
        $allergyMatch = null;
        $highestSeverity = null;

        foreach ($drugAllergies as $allergy) {
            // Check for exact match or partial match in medicine name, generic name, or brand name
            $allergenName = strtolower($allergy->allergen_name);
            $medicineName = strtolower($medicine->medicine_name);
            $genericName = strtolower($medicine->generic_name ?? '');
            $brandName = strtolower($medicine->brand_name ?? '');

            // Check if allergy matches any of the medicine names
            if (
                str_contains($medicineName, $allergenName) ||
                str_contains($allergenName, $medicineName) ||
                str_contains($genericName, $allergenName) ||
                str_contains($allergenName, $genericName) ||
                str_contains($brandName, $allergenName) ||
                str_contains($allergenName, $brandName)
            ) {
                // Found a potential match
                if (!$allergyMatch || $this->compareSeverity($allergy->severity, $highestSeverity) > 0) {
                    $allergyMatch = $allergy;
                    $highestSeverity = $allergy->severity;
                }
            }
        }

        if ($allergyMatch) {
            // Determine if prescription is absolutely prohibited
            $canPrescribe = !in_array($allergyMatch->severity, ['Life-threatening', 'Severe']);

            return [
                'has_allergy' => true,
                'severity' => $allergyMatch->severity,
                'details' => $allergyMatch,
                'warning_message' => $this->getWarningMessage($allergyMatch, $medicine),
                'can_prescribe' => $canPrescribe,
                'requires_override' => !$canPrescribe,
            ];
        }

        return [
            'has_allergy' => false,
            'severity' => null,
            'details' => null,
            'warning_message' => null,
            'can_prescribe' => true,
        ];
    }

    /**
     * ✅ NEW: Generate appropriate warning message based on severity
     */
    private function getWarningMessage($allergy, $medicine)
    {
        $medicineName = $medicine->medicine_name;
        $allergen = $allergy->allergen_name;
        $severity = $allergy->severity;
        $reaction = $allergy->reaction_description;

        $message = "⚠️ ALLERGY ALERT: Patient is allergic to {$allergen} (Severity: {$severity})";
        
        if ($reaction) {
            $message .= " - Known reaction: {$reaction}";
        }

        switch ($severity) {
            case 'Life-threatening':
                $message .= "\n\n🚨 CRITICAL WARNING: This medication may contain or be related to {$allergen}. DO NOT PRESCRIBE without consulting specialist.";
                break;
            case 'Severe':
                $message .= "\n\n⛔ SEVERE ALLERGY: This medication should NOT be prescribed. Consider alternative medications.";
                break;
            case 'Moderate':
                $message .= "\n\n⚡ CAUTION: Use alternative medication if available. If necessary, prescribe with antihistamine prophylaxis and close monitoring.";
                break;
            case 'Mild':
                $message .= "\n\nℹ️ MILD ALLERGY: Exercise caution. Consider alternative medications or prescribe with monitoring.";
                break;
        }

        return $message;
    }

    /**
     * ✅ NEW: Compare severity levels
     */
    private function compareSeverity($severity1, $severity2)
    {
        $severityRanking = [
            'Mild' => 1,
            'Moderate' => 2,
            'Severe' => 3,
            'Life-threatening' => 4,
        ];

        $rank1 = $severityRanking[$severity1] ?? 0;
        $rank2 = $severityRanking[$severity2] ?? 0;

        return $rank1 - $rank2;
    }

    /**
     * ✅ NEW: Get alternative medications (when allergy found)
     * Returns medications in the same category that patient is NOT allergic to
     */
    public function getAlternatives(Request $request)
    {
        $medicineId = $request->get('medicine_id');
        $patientId = $request->get('patient_id');

        $currentMedicine = MedicineInventory::findOrFail($medicineId);

        // Find alternatives in the same category
        $alternatives = MedicineInventory::where('status', 'Active')
            ->where('quantity_in_stock', '>', 0)
            ->where('category', $currentMedicine->category)
            ->where('medicine_id', '!=', $medicineId)
            ->limit(5)
            ->get();

        // Check allergies for each alternative
        $safeAlternatives = $alternatives->filter(function($medicine) use ($patientId) {
            $allergyCheck = $this->checkMedicineAllergy($medicine, $patientId);
            return !$allergyCheck['has_allergy'];
        });

        return response()->json([
            'current_medicine' => $currentMedicine->medicine_name,
            'category' => $currentMedicine->category,
            'alternatives' => $safeAlternatives,
            'has_safe_alternatives' => $safeAlternatives->isNotEmpty(),
        ]);
    }
}