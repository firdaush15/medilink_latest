<?php
// app/Http/Controllers/Pharmacist/PharmacistReportController.php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrescriptionDispensing;
use App\Models\MedicineInventory;
use App\Models\StockMovement;
use App\Models\MedicineDisposal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PharmacistReportController extends Controller
{
    /**
     * Display reports dashboard with real statistics
     */
    public function index()
    {
        // ========================================
        // 1. GET CURRENT MONTH STATISTICS
        // ========================================
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();
        
        // Prescriptions this month
        $prescriptionsThisMonth = PrescriptionDispensing::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $prescriptionsLastMonth = PrescriptionDispensing::whereMonth('created_at', $previousMonth->month)
            ->whereYear('created_at', $previousMonth->year)
            ->count();
        
        $prescriptionChange = $prescriptionsLastMonth > 0 
            ? round((($prescriptionsThisMonth - $prescriptionsLastMonth) / $prescriptionsLastMonth) * 100) 
            : 0;
        
        // Verified prescriptions this month
        $verifiedThisMonth = PrescriptionDispensing::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('verification_status', ['Verified', 'Dispensed'])
            ->count();
        
        $verifiedLastMonth = PrescriptionDispensing::whereMonth('created_at', $previousMonth->month)
            ->whereYear('created_at', $previousMonth->year)
            ->whereIn('verification_status', ['Verified', 'Dispensed'])
            ->count();
        
        $verifiedChange = $verifiedLastMonth > 0 
            ? round((($verifiedThisMonth - $verifiedLastMonth) / $verifiedLastMonth) * 100) 
            : 0;
        
        // Total revenue this month
        $revenueThisMonth = PrescriptionDispensing::whereMonth('dispensed_at', now()->month)
            ->whereYear('dispensed_at', now()->year)
            ->where('verification_status', 'Dispensed')
            ->sum('total_amount');
        
        $revenueLastMonth = PrescriptionDispensing::whereMonth('dispensed_at', $previousMonth->month)
            ->whereYear('dispensed_at', $previousMonth->year)
            ->where('verification_status', 'Dispensed')
            ->sum('total_amount');
        
        $revenueChange = $revenueLastMonth > 0 
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100) 
            : 0;
        
        // Items dispensed this month
        $itemsThisMonth = StockMovement::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('movement_type', 'Dispensed')
            ->sum(DB::raw('ABS(quantity)'));
        
        $itemsLastMonth = StockMovement::whereMonth('created_at', $previousMonth->month)
            ->whereYear('created_at', $previousMonth->year)
            ->where('movement_type', 'Dispensed')
            ->sum(DB::raw('ABS(quantity)'));
        
        $itemsChange = $itemsLastMonth > 0 
            ? round((($itemsThisMonth - $itemsLastMonth) / $itemsLastMonth) * 100) 
            : 0;
        
        // ========================================
        // 2. GET CATEGORY-SPECIFIC COUNTS
        // ========================================
        
        // Inventory counts
        $totalMedicines = MedicineInventory::count();
        $lowStockCount = MedicineInventory::lowStock()->count();
        $outOfStockCount = MedicineInventory::outOfStock()->count();
        $expiredCount = MedicineInventory::expired()->count();
        $expiringCriticalCount = MedicineInventory::expiringCritical()->count();
        
        // Prescription counts
        $pendingPrescriptions = PrescriptionDispensing::where('verification_status', 'Pending')->count();
        $dispensedPrescriptions = PrescriptionDispensing::where('verification_status', 'Dispensed')
            ->whereMonth('dispensed_at', now()->month)
            ->count();
        $rejectedPrescriptions = PrescriptionDispensing::where('verification_status', 'Rejected')
            ->whereMonth('created_at', now()->month)
            ->count();
        
        // ========================================
        // 3. PREPARE DATA FOR VIEW
        // ========================================
        
        $stats = [
            'prescriptions' => [
                'count' => $prescriptionsThisMonth,
                'change' => $prescriptionChange,
            ],
            'verified' => [
                'count' => $verifiedThisMonth,
                'change' => $verifiedChange,
            ],
            'revenue' => [
                'amount' => $revenueThisMonth,
                'change' => $revenueChange,
            ],
            'items_dispensed' => [
                'count' => $itemsThisMonth,
                'change' => $itemsChange,
            ],
        ];
        
        $inventory_counts = [
            'total' => $totalMedicines,
            'low_stock' => $lowStockCount,
            'out_of_stock' => $outOfStockCount,
            'expired' => $expiredCount,
            'expiring_soon' => $expiringCriticalCount,
        ];
        
        $prescription_counts = [
            'pending' => $pendingPrescriptions,
            'dispensed' => $dispensedPrescriptions,
            'rejected' => $rejectedPrescriptions,
        ];
        
        return view('pharmacist.pharmacist_reports', compact(
            'stats',
            'inventory_counts',
            'prescription_counts'
        ));
    }

    /**
     * Generate custom report based on filters
     */
    public function generateCustomReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:inventory,prescriptions,sales,stock-movements',
            'date_range' => 'required|in:today,week,month,quarter,year,custom',
            'start_date' => 'required_if:date_range,custom|date',
            'end_date' => 'required_if:date_range,custom|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,csv,excel',
            'group_by' => 'nullable|in:category,supplier,doctor,date',
        ]);

        // Calculate date range
        [$startDate, $endDate] = $this->calculateDateRange(
            $validated['date_range'],
            $request->start_date,
            $request->end_date
        );

        // Generate report based on type
        $data = match($validated['report_type']) {
            'inventory' => $this->generateInventoryReport($startDate, $endDate, $validated['group_by']),
            'prescriptions' => $this->generatePrescriptionReport($startDate, $endDate, $validated['group_by']),
            'sales' => $this->generateSalesReport($startDate, $endDate, $validated['group_by']),
            'stock-movements' => $this->generateStockMovementReport($startDate, $endDate, $validated['group_by']),
        };

        // Export based on format
        return match($validated['format']) {
            'csv' => $this->exportToCsv($data, $validated['report_type']),
            'excel' => $this->exportToExcel($data, $validated['report_type']),
            'pdf' => $this->exportToPdf($data, $validated['report_type']),
        };
    }

    /**
     * Calculate date range based on selection
     */
    private function calculateDateRange($range, $customStart = null, $customEnd = null)
    {
        return match($range) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [Carbon::parse($customStart), Carbon::parse($customEnd)],
        };
    }

    /**
     * Generate inventory report
     */
    private function generateInventoryReport($startDate, $endDate, $groupBy = null)
    {
        $query = MedicineInventory::with('batches');

        if ($groupBy === 'category') {
            return $query->select('category', DB::raw('COUNT(*) as total'), DB::raw('SUM(quantity_in_stock) as total_stock'))
                ->groupBy('category')
                ->get();
        }

        return $query->get()->map(function($medicine) {
            return [
                'medicine_name' => $medicine->medicine_name,
                'category' => $medicine->category,
                'current_stock' => $medicine->quantity_in_stock,
                'reorder_level' => $medicine->reorder_level,
                'status' => $medicine->status,
                'unit_price' => $medicine->unit_price,
                'total_value' => $medicine->quantity_in_stock * $medicine->unit_price,
            ];
        });
    }

    /**
     * Generate prescription report
     */
    private function generatePrescriptionReport($startDate, $endDate, $groupBy = null)
    {
        $query = PrescriptionDispensing::with(['prescription.doctor.user', 'prescription.patient.user'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($groupBy === 'doctor') {
            return $query->select('prescription_id', DB::raw('COUNT(*) as total'))
                ->join('prescriptions', 'prescription_dispensings.prescription_id', '=', 'prescriptions.prescription_id')
                ->groupBy('prescriptions.doctor_id')
                ->with('prescription.doctor.user')
                ->get();
        }

        return $query->get()->map(function($dispensing) {
            return [
                'date' => $dispensing->created_at->format('Y-m-d'),
                'patient' => $dispensing->patient->user->name,
                'doctor' => $dispensing->prescription->doctor->user->name ?? 'N/A',
                'status' => $dispensing->verification_status,
                'total_amount' => $dispensing->total_amount,
                'dispensed_at' => $dispensing->dispensed_at?->format('Y-m-d H:i'),
            ];
        });
    }

    /**
     * Generate sales report
     */
    private function generateSalesReport($startDate, $endDate, $groupBy = null)
    {
        $query = PrescriptionDispensing::where('verification_status', 'Dispensed')
            ->whereBetween('dispensed_at', [$startDate, $endDate]);

        if ($groupBy === 'date') {
            return $query->select(
                    DB::raw('DATE(dispensed_at) as date'),
                    DB::raw('COUNT(*) as total_prescriptions'),
                    DB::raw('SUM(total_amount) as total_revenue')
                )
                ->groupBy(DB::raw('DATE(dispensed_at)'))
                ->orderBy('date', 'desc')
                ->get();
        }

        return $query->get()->map(function($dispensing) {
            return [
                'date' => $dispensing->dispensed_at->format('Y-m-d'),
                'prescription_id' => $dispensing->prescription_id,
                'patient' => $dispensing->patient->user->name,
                'amount' => $dispensing->total_amount,
                'payment_method' => $dispensing->payment_method,
            ];
        });
    }

    /**
     * Generate stock movement report
     */
    private function generateStockMovementReport($startDate, $endDate, $groupBy = null)
    {
        $query = StockMovement::with('medicine')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($groupBy === 'category') {
            return $query->join('medicine_inventory', 'stock_movements.medicine_id', '=', 'medicine_inventory.medicine_id')
                ->select(
                    'medicine_inventory.category',
                    DB::raw('SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) as stock_in'),
                    DB::raw('SUM(CASE WHEN quantity < 0 THEN ABS(quantity) ELSE 0 END) as stock_out')
                )
                ->groupBy('medicine_inventory.category')
                ->get();
        }

        return $query->get()->map(function($movement) {
            return [
                'date' => $movement->created_at->format('Y-m-d H:i'),
                'medicine' => $movement->medicine->medicine_name,
                'movement_type' => $movement->movement_type,
                'quantity' => $movement->quantity,
                'balance_after' => $movement->balance_after,
                'notes' => $movement->notes,
            ];
        });
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($data, $reportType)
    {
        $filename = "{$reportType}_report_" . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            if ($data->count() > 0) {
                fputcsv($file, array_keys($data->first()->toArray()));
            }
            
            // Add data
            foreach ($data as $row) {
                fputcsv($file, $row->toArray());
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel (using CSV format for simplicity)
     */
    private function exportToExcel($data, $reportType)
    {
        // For now, use CSV format. Later can integrate PhpSpreadsheet for true Excel
        return $this->exportToCsv($data, $reportType);
    }

    /**
     * Export to PDF (placeholder - requires PDF library)
     */
    private function exportToPdf($data, $reportType)
    {
        // TODO: Implement PDF generation using DomPDF or similar
        return response()->json([
            'message' => 'PDF generation coming soon',
            'data' => $data
        ]);
    }
}