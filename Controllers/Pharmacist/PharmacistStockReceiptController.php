<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\StockReceipt;
use App\Models\RestockRequest;
use App\Models\MedicineInventory;
use Illuminate\Http\Request;

class PharmacistStockReceiptController extends Controller
{
    /**
     * Show all stock receipts
     */
    public function index()
    {
        $pharmacist = auth()->user()->pharmacist;

        $receipts = StockReceipt::with(['medicine', 'restockRequest', 'receivedBy'])
            ->where('received_by', $pharmacist->pharmacist_id)
            ->latest('received_at')
            ->paginate(15);

        return view('pharmacist.pharmacist_receipts', compact('receipts'));
    }

    /**
     * Show create receipt form (for approved/ordered requests)
     */
    public function create(Request $request)
    {
        $pharmacist = auth()->user()->pharmacist;

        // Get approved/ordered requests that can be received
        $pendingRequests = RestockRequest::with('medicine')
            ->where('requested_by', $pharmacist->pharmacist_id)
            ->whereIn('status', ['Approved', 'Ordered', 'Partially Received'])
            ->latest('created_at')
            ->get();

        // Pre-select request if provided
        $selectedRequest = null;
        if ($request->filled('request_id')) {
            $selectedRequest = RestockRequest::find($request->request_id);
        }

        return view('pharmacist.pharmacist_receiptCreate', compact('pendingRequests', 'selectedRequest'));
    }

    /**
     * Store stock receipt
     */
    public function store(Request $request)
    {
        // 1. Validation
        $validated = $request->validate([
            'restock_request_id' => 'nullable|exists:restock_requests,request_id',
            'medicine_id' => 'required|exists:medicine_inventory,medicine_id',
            'quantity_received' => 'required|integer|min:1',
            'batch_number' => 'required|string|max:100',
            'manufacture_date' => 'nullable|date|before_or_equal:today',
            'expiry_date' => 'required|date|after:today',
            'supplier' => 'required|string|max:255',
            'supplier_invoice_number' => 'nullable|string|max:100',
            'unit_price' => 'required|numeric|min:0',
            'quality_check_notes' => 'nullable|string|max:1000',
            'packaging_intact' => 'nullable|in:0,1',
            'temperature_maintained' => 'nullable|in:0,1',
            'expiry_acceptable' => 'nullable|in:0,1',
        ]);

        $pharmacist = auth()->user()->pharmacist;

        // 2. Convert checkbox values to boolean
        $validated['packaging_intact'] = ($request->input('packaging_intact') == '1');
        $validated['temperature_maintained'] = ($request->input('temperature_maintained') == '1');
        $validated['expiry_acceptable'] = ($request->input('expiry_acceptable') == '1');

        // 3. Quality check: Ensure expiry is at least 1 year from now
        $expiryDate = \Carbon\Carbon::parse($validated['expiry_date']);
        $monthsUntilExpiry = now()->diffInMonths($expiryDate);

        if ($monthsUntilExpiry < 12) {
            $validated['expiry_acceptable'] = false;
            $validated['quality_check_notes'] = ($validated['quality_check_notes'] ?? '') .
                " WARNING: Expiry date less than 1 year ({$monthsUntilExpiry} months).";
        }

        // 4. Determine quality status
        $qualityStatus = 'Accepted';
        if (!$validated['packaging_intact'] || !$validated['temperature_maintained'] || !$validated['expiry_acceptable']) {
            $qualityStatus = 'On Hold';
        }

        // 5. Prepare data for insertion
        $validated['received_by'] = $pharmacist->pharmacist_id;
        $validated['quality_status'] = $qualityStatus;
        $validated['received_at'] = now();

        // ✅ FIX: Calculate total_cost before creating the record
        $validated['total_cost'] = $validated['quantity_received'] * $validated['unit_price'];

        try {
            $receipt = StockReceipt::create($validated);

            // Alert if quality issues
            if ($qualityStatus !== 'Accepted') {
                \App\Models\StaffAlert::create([
                    'sender_id' => auth()->id(),
                    'sender_type' => 'pharmacist',
                    'recipient_id' => 1, // Admin
                    'recipient_type' => 'admin',
                    'medicine_id' => $validated['medicine_id'],
                    'alert_type' => 'Quality Issue',
                    'priority' => 'High',
                    'alert_title' => 'Stock Receipt Quality Issue',
                    'alert_message' => "Receipt {$receipt->receipt_number} has quality concerns. Review required.",
                    'action_url' => route('pharmacist.receipts.show', $receipt->receipt_id),
                ]);
            }

            return redirect()
                ->route('pharmacist.receipts.index')
                ->with('success', "✅ Stock receipt {$receipt->receipt_number} recorded successfully!");
        } catch (\Exception $e) {
            \Log::error('Stock receipt creation failed: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', '❌ Failed to record receipt: ' . $e->getMessage());
        }
    }

    /**
     * Show receipt details
     */
    public function show($id)
    {
        $pharmacist = auth()->user()->pharmacist;

        $receipt = StockReceipt::with(['medicine', 'restockRequest', 'receivedBy'])
            ->where('received_by', $pharmacist->pharmacist_id)
            ->findOrFail($id);

        return view('pharmacist.pharmacist_receiptShow', compact('receipt'));
    }
}
