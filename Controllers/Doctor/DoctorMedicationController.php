<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\MedicineInventory;
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
     * ✅ FIXED: AJAX search for medications with unit price
     * Returns JSON data for autocomplete
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        
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
                'unit_price' // ✅ ADDED: Include unit price for cost calculation
            ]);

        return response()->json($medicines);
    }

    /**
     * Check medication availability
     * Returns stock status for a specific medicine
     */
    public function checkAvailability($id)
    {
        $medicine = MedicineInventory::findOrFail($id);

        return response()->json([
            'available' => $medicine->quantity_in_stock > 0,
            'quantity' => $medicine->quantity_in_stock,
            'status' => $medicine->status,
            'is_low_stock' => $medicine->isLowStock(),
            'unit_price' => $medicine->unit_price, // ✅ Include price
        ]);
    }
}