<?php
// ========================================
// app/Http/Controllers/Pharmacist/PharmacistRestockController.php
// ========================================

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\RestockRequest;
use App\Models\MedicineInventory;
use Illuminate\Http\Request;

class PharmacistRestockController extends Controller
{
    /**
     * Show all restock requests (created by this pharmacist)
     */
    public function index(Request $request)
    {
        $pharmacist = auth()->user()->pharmacist;
        
        $query = RestockRequest::with(['medicine', 'approvedBy'])
            ->where('requested_by', $pharmacist->pharmacist_id);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        $requests = $query->latest('created_at')->paginate(15);
        
        // Statistics
        $stats = [
            'pending' => RestockRequest::where('requested_by', $pharmacist->pharmacist_id)
                ->where('status', 'Pending')->count(),
            'approved' => RestockRequest::where('requested_by', $pharmacist->pharmacist_id)
                ->where('status', 'Approved')->count(),
            'ordered' => RestockRequest::where('requested_by', $pharmacist->pharmacist_id)
                ->where('status', 'Ordered')->count(),
            'rejected' => RestockRequest::where('requested_by', $pharmacist->pharmacist_id)
                ->where('status', 'Rejected')->count(),
        ];
        
        return view('pharmacist.pharmacist_restockRequests', compact('requests', 'stats'));
    }
    
    /**
     * Show create restock request form
     */
    public function create(Request $request)
    {
        // Get medicines that need restocking
        $lowStockMedicines = MedicineInventory::whereRaw('quantity_in_stock <= reorder_level')
            ->where('status', '!=', 'Expired')
            ->orderBy('medicine_name')
            ->get();
        
        // Pre-select medicine if provided
        $selectedMedicine = null;
        if ($request->filled('medicine_id')) {
            $selectedMedicine = MedicineInventory::find($request->medicine_id);
        }
        
        return view('pharmacist.pharmacist_restockCreate', compact('lowStockMedicines', 'selectedMedicine'));
    }
    
    /**
     * Store new restock request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'medicine_id' => 'required|exists:medicine_inventory,medicine_id',
            'quantity_requested' => 'required|integer|min:1',
            'priority' => 'required|in:Normal,Urgent,Critical',
            'justification' => 'required|string|max:1000',
            'preferred_supplier' => 'nullable|string|max:255',
            'estimated_unit_price' => 'nullable|numeric|min:0',
        ]);
        
        $pharmacist = auth()->user()->pharmacist;
        $medicine = MedicineInventory::findOrFail($validated['medicine_id']);
        
        $restockRequest = RestockRequest::create([
            'medicine_id' => $validated['medicine_id'],
            'requested_by' => $pharmacist->pharmacist_id,
            'current_stock' => $medicine->quantity_in_stock,
            'quantity_requested' => $validated['quantity_requested'],
            'priority' => $validated['priority'],
            'justification' => $validated['justification'],
            'preferred_supplier' => $validated['preferred_supplier'],
            'estimated_unit_price' => $validated['estimated_unit_price'],
            'status' => 'Pending',
        ]);
        
        // Create alert for admin
        \App\Models\StaffAlert::create([
            'sender_id' => auth()->id(),
            'sender_type' => 'pharmacist',
            'recipient_id' => 1, // Admin user ID (adjust as needed)
            'recipient_type' => 'admin',
            'medicine_id' => $medicine->medicine_id,
            'alert_type' => 'Restock Request',
            'priority' => $validated['priority'],
            'alert_title' => "New {$validated['priority']} Restock Request",
            'alert_message' => "Pharmacist {$pharmacist->user->name} requests {$validated['quantity_requested']} units of {$medicine->medicine_name}",
            'action_url' => route('admin.restock.show', $restockRequest->request_id),
        ]);
        
        return redirect()
            ->route('pharmacist.restock.index')
            ->with('success', "Restock request {$restockRequest->request_number} created successfully!");
    }
    
    /**
     * Show restock request details
     */
    public function show($id)
    {
        $pharmacist = auth()->user()->pharmacist;
        
        $request = RestockRequest::with(['medicine', 'approvedBy', 'receipts.receivedBy', 'logs.performedBy'])
            ->where('requested_by', $pharmacist->pharmacist_id)
            ->findOrFail($id);
        
        return view('pharmacist.pharmacist_restockShow', compact('request'));
    }
    
    /**
     * Mark request as ordered (after sending PO to supplier)
     */
    public function markAsOrdered(Request $request, $id)
    {
        $validated = $request->validate([
            'purchase_order_number' => 'required|string|max:100',
            'expected_delivery_date' => 'nullable|date|after:today',
        ]);
        
        $pharmacist = auth()->user()->pharmacist;
        
        $restockRequest = RestockRequest::where('requested_by', $pharmacist->pharmacist_id)
            ->findOrFail($id);
        
        if (!$restockRequest->canOrder()) {
            return back()->with('error', 'This request cannot be marked as ordered.');
        }
        
        $restockRequest->markAsOrdered(
            $validated['purchase_order_number'],
            $validated['expected_delivery_date'] ?? null
        );
        
        return back()->with('success', 'Request marked as ordered successfully!');
    }
    
    /**
     * Cancel a pending request
     */
    public function cancel($id)
    {
        $pharmacist = auth()->user()->pharmacist;
        
        $request = RestockRequest::where('requested_by', $pharmacist->pharmacist_id)
            ->findOrFail($id);
        
        if (!$request->isPending()) {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }
        
        $request->update(['status' => 'Cancelled']);
        
        $request->logAction('cancelled', 'Pending', 'Cancelled', auth()->id(), 'pharmacist', 'Cancelled by pharmacist');
        
        return back()->with('success', 'Restock request cancelled successfully!');
    }
}

