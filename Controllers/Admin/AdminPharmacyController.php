<?php
// app/Http/Controllers/Admin/AdminPharmacyController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicineInventory;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class AdminPharmacyController extends Controller
{
    /**
     * Display pharmacy inventory overview
     */
    public function index(Request $request)
    {
        // Base query
        $query = MedicineInventory::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('medicine_name', 'LIKE', "%{$search}%")
                  ->orWhere('generic_name', 'LIKE', "%{$search}%")
                  ->orWhere('brand_name', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%")
                  ->orWhere('supplier', 'LIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Expiry filter
        if ($request->filled('expiry')) {
            switch ($request->expiry) {
                case 'critical':
                    // Medicines expiring in 90 days or less
                    $query->where('expiry_date', '<=', now()->addDays(90))
                          ->where('expiry_date', '>', now());
                    break;
                case 'warning':
                    // Medicines expiring between 91-180 days
                    $query->where('expiry_date', '<=', now()->addDays(180))
                          ->where('expiry_date', '>', now()->addDays(90));
                    break;
                case 'safe':
                    // Medicines expiring after 180 days
                    $query->where('expiry_date', '>', now()->addDays(180));
                    break;
            }
        }

        // Get paginated medicines
        $medicines = $query->orderBy('medicine_name', 'asc')->paginate(15);

        // Get statistics
        $totalMedicines = MedicineInventory::count();
        $lowStockCount = MedicineInventory::where('status', 'Low Stock')->count();
        $outOfStockCount = MedicineInventory::where('status', 'Out of Stock')->count();
        
        // Count medicines expiring within 90 days
        $expiringCount = MedicineInventory::where('expiry_date', '<=', now()->addDays(90))
            ->where('expiry_date', '>', now())
            ->where('status', '!=', 'Expired')
            ->count();

        // Get critical alerts (out of stock + expiring critically)
        $criticalAlerts = MedicineInventory::where(function($q) {
            $q->where('status', 'Out of Stock')
              ->orWhere(function($q2) {
                  $q2->where('expiry_date', '<=', now()->addDays(90))
                     ->where('expiry_date', '>', now());
              });
        })->get();

        // Get all unique categories for filter dropdown
        $categories = MedicineInventory::distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        // Get recent stock movements (last 10)
        $recentMovements = StockMovement::with(['medicine', 'pharmacist.user'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.admin_pharmacyInventory', compact(
            'medicines',
            'totalMedicines',
            'lowStockCount',
            'outOfStockCount',
            'expiringCount',
            'criticalAlerts',
            'categories',
            'recentMovements'
        ));
    }

    /**
     * Display specific medicine details
     */
    public function show($id)
    {
        $medicine = MedicineInventory::with(['stockMovements.pharmacist.user'])
            ->findOrFail($id);

        // Get related prescriptions using this medicine
        $prescriptionCount = \App\Models\PrescriptionItem::where('medicine_name', $medicine->medicine_name)
            ->count();

        // Get stock movement history
        $stockHistory = StockMovement::where('medicine_id', $medicine->medicine_id)
            ->with('pharmacist.user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.pharmacy-inventory-details', compact(
            'medicine',
            'prescriptionCount',
            'stockHistory'
        ));
    }

    /**
     * Generate inventory reports
     */
    public function reports()
    {
        // Get comprehensive statistics
        $stats = [
            'total_medicines' => MedicineInventory::count(),
            'active_medicines' => MedicineInventory::where('status', 'Active')->count(),
            'low_stock' => MedicineInventory::where('status', 'Low Stock')->count(),
            'out_of_stock' => MedicineInventory::where('status', 'Out of Stock')->count(),
            'expired' => MedicineInventory::where('status', 'Expired')->count(),
            
            // Value calculations
            'total_inventory_value' => MedicineInventory::selectRaw('SUM(quantity_in_stock * unit_price) as total')
                ->value('total'),
            
            // Category breakdown
            'by_category' => MedicineInventory::selectRaw('category, COUNT(*) as count, SUM(quantity_in_stock) as total_stock')
                ->groupBy('category')
                ->get(),
            
            // Stock movements this month
            'movements_this_month' => StockMovement::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            
            // Top dispensed medicines
            'top_dispensed' => StockMovement::where('movement_type', 'Dispensed')
                ->selectRaw('medicine_id, COUNT(*) as dispense_count')
                ->groupBy('medicine_id')
                ->orderByDesc('dispense_count')
                ->limit(10)
                ->with('medicine')
                ->get(),
        ];

        return view('admin.pharmacy-reports', compact('stats'));
    }

    /**
     * Display analytics dashboard
     */
    public function analytics()
    {
        // Get trend data for charts (last 6 months)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyData[] = [
                'month' => $month->format('M Y'),
                'stock_in' => StockMovement::where('movement_type', 'Stock In')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('quantity'),
                'dispensed' => StockMovement::where('movement_type', 'Dispensed')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('quantity'),
            ];
        }

        // Category distribution
        $categoryData = MedicineInventory::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get();

        // Expiry timeline (next 12 months)
        $expiryTimeline = [];
        for ($i = 0; $i < 12; $i++) {
            $month = now()->addMonths($i);
            $expiryTimeline[] = [
                'month' => $month->format('M Y'),
                'count' => MedicineInventory::whereMonth('expiry_date', $month->month)
                    ->whereYear('expiry_date', $month->year)
                    ->count(),
            ];
        }

        return view('admin.pharmacy-analytics', compact(
            'monthlyData',
            'categoryData',
            'expiryTimeline'
        ));
    }

    /**
     * Export inventory data
     */
    public function export(Request $request)
    {
        $medicines = MedicineInventory::all();

        $filename = 'pharmacy_inventory_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($medicines) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Medicine ID',
                'Medicine Name',
                'Generic Name',
                'Brand Name',
                'Category',
                'Form',
                'Strength',
                'Quantity in Stock',
                'Reorder Level',
                'Unit Price',
                'Supplier',
                'Batch Number',
                'Manufacture Date',
                'Expiry Date',
                'Status',
                'Requires Prescription',
                'Controlled Substance',
            ]);

            // CSV data
            foreach ($medicines as $medicine) {
                fputcsv($file, [
                    $medicine->medicine_id,
                    $medicine->medicine_name,
                    $medicine->generic_name,
                    $medicine->brand_name,
                    $medicine->category,
                    $medicine->form,
                    $medicine->strength,
                    $medicine->quantity_in_stock,
                    $medicine->reorder_level,
                    $medicine->unit_price,
                    $medicine->supplier,
                    $medicine->batch_number,
                    $medicine->manufacture_date?->format('Y-m-d'),
                    $medicine->expiry_date?->format('Y-m-d'),
                    $medicine->status,
                    $medicine->requires_prescription ? 'Yes' : 'No',
                    $medicine->is_controlled_substance ? 'Yes' : 'No',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}