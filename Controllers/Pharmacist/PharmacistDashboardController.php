<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\PrescriptionDispensing;
use App\Models\MedicineInventory;
use App\Models\StaffAlert;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PharmacistDashboardController extends Controller
{
    public function index()
    {
        $pharmacist = Auth::user()->pharmacist;

        // ========================================
        // PRESCRIPTION STATISTICS
        // ========================================
        
        // Pending prescriptions needing verification
        $pendingPrescriptions = PrescriptionDispensing::where('verification_status', 'Pending')
            ->count();

        // Verified prescriptions ready to dispense
        $verifiedPrescriptions = PrescriptionDispensing::where('verification_status', 'Verified')
            ->whereNull('dispensed_at')
            ->count();

        // Dispensed today
        $dispensedToday = PrescriptionDispensing::where('verification_status', 'Dispensed')
            ->whereDate('dispensed_at', today())
            ->count();

        // Today's revenue
        $todayRevenue = PrescriptionDispensing::where('verification_status', 'Dispensed')
            ->whereDate('dispensed_at', today())
            ->sum('total_amount') ?? 0;

        // ========================================
        // INVENTORY STATISTICS - REAL-WORLD HOSPITAL STANDARDS
        // ========================================
        
        // Low stock items (excluding out of stock)
        $lowStockCount = MedicineInventory::whereRaw('quantity_in_stock <= reorder_level')
            ->where('quantity_in_stock', '>', 0)
            ->count();

        // Out of stock items - CRITICAL
        $outOfStockCount = MedicineInventory::where('quantity_in_stock', '=', 0)->count();

        // Expiring soon (within 180 days / 6 months) - includes both critical and warning zones
        $expiringCount = MedicineInventory::where('expiry_date', '<=', now()->addDays(180))
            ->where('expiry_date', '>', now())
            ->count();
        
        // Expired items
        $expiredCount = MedicineInventory::where('expiry_date', '<=', now())->count();

        // ========================================
        // CRITICAL ALERTS - USING STAFFALERT
        // ========================================
        $criticalAlerts = StaffAlert::with(['medicine', 'prescription', 'patient.user'])
            ->where('recipient_id', auth()->id())
            ->where('recipient_type', 'pharmacist')
            ->where('priority', 'Critical')
            ->where('is_acknowledged', false)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // ========================================
        // RECENT PRESCRIPTIONS REQUIRING ACTION
        // ========================================
        $recentPrescriptions = PrescriptionDispensing::with([
            'prescription.doctor.user',
            'prescription.items',
            'patient.user'
        ])
        ->whereIn('verification_status', ['Pending', 'Verified'])
        ->orderByRaw("FIELD(verification_status, 'Pending', 'Verified')")
        ->orderBy('created_at', 'asc')
        ->take(10)
        ->get();

        // ========================================
        // INVENTORY ALERTS - USING STAFFALERT
        // ========================================
        $inventoryAlerts = StaffAlert::with(['medicine'])
            ->where('recipient_id', auth()->id())
            ->where('recipient_type', 'pharmacist')
            ->whereIn('alert_type', ['Low Stock', 'Expiring Soon', 'Expired Medicine', 'Restock Needed'])
            ->where('is_acknowledged', false)
            ->orderByRaw("FIELD(priority, 'Critical', 'Urgent', 'High', 'Normal')")
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        return view('pharmacist.pharmacist_dashboard', compact(
            'pendingPrescriptions',
            'verifiedPrescriptions',
            'dispensedToday',
            'todayRevenue',
            'lowStockCount',
            'outOfStockCount',
            'expiringCount',
            'expiredCount',
            'criticalAlerts',
            'recentPrescriptions',
            'inventoryAlerts'
        ));
    }
}