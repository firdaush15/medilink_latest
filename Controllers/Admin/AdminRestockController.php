<?php
// ========================================
// app/Http/Controllers/Admin/AdminRestockController.php
// ========================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RestockRequest;
use App\Models\StockReceipt;
use App\Models\MedicineDisposal;
use Illuminate\Http\Request;

class AdminRestockController extends Controller
{
    /**
     * Show all restock requests (Admin overview) - UPDATED WITH FILTERS
     */
    public function index(Request $request)
    {
        $query = RestockRequest::with(['medicine', 'requestedBy.user', 'approvedBy']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: Show pending requests first, then others
            $query->orderByRaw("FIELD(status, 'Pending', 'Approved', 'Ordered', 'Partially Received', 'Received', 'Rejected', 'Cancelled')");
        }
        
        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        // Order by priority (Critical first) and date
        $query->orderByRaw("FIELD(priority, 'Critical', 'Urgent', 'Normal')")
              ->latest('created_at');
        
        $requests = $query->paginate(15)->appends($request->query());
        
        // Statistics
        $stats = [
            'pending' => RestockRequest::where('status', 'Pending')->count(),
            'approved' => RestockRequest::where('status', 'Approved')
                ->whereMonth('approved_at', now()->month)
                ->count(),
            'ordered' => RestockRequest::where('status', 'Ordered')->count(),
            'critical_pending' => RestockRequest::where('status', 'Pending')
                ->where('priority', 'Critical')->count(),
            'total_value_pending' => RestockRequest::where('status', 'Pending')
                ->sum('estimated_total_cost'),
        ];
        
        return view('admin.admin_restock', compact('requests', 'stats'));
    }
    
    /**
     * Show restock request details
     */
    public function show($id)
    {
        $request = RestockRequest::with([
            'medicine',
            'requestedBy.user',
            'approvedBy',
            'receipts.receivedBy.user',
            'logs.performedBy'
        ])->findOrFail($id);
        
        return view('admin.admin_restockShow', compact('request'));
    }
    
    /**
     * Approve restock request
     */
    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);
        
        $restockRequest = RestockRequest::findOrFail($id);
        
        if (!$restockRequest->canApprove()) {
            return back()->with('error', 'This request cannot be approved. Current status: ' . $restockRequest->status);
        }
        
        $restockRequest->approve(auth()->id(), $validated['approval_notes'] ?? null);
        
        // Notify pharmacist
        \App\Models\StaffAlert::create([
            'sender_id' => auth()->id(),
            'sender_type' => 'admin',
            'recipient_id' => $restockRequest->requestedBy->user_id,
            'recipient_type' => 'pharmacist',
            'medicine_id' => $restockRequest->medicine_id,
            'alert_type' => 'Restock Approved',
            'priority' => $restockRequest->priority === 'Critical' ? 'Critical' : 'Normal',
            'alert_title' => 'Restock Request Approved',
            'alert_message' => "Your restock request {$restockRequest->request_number} for {$restockRequest->medicine->medicine_name} has been approved. You may proceed with ordering.",
            'action_url' => route('pharmacist.restock.show', $restockRequest->request_id),
        ]);
        
        return redirect()->route('admin.restock.index')
            ->with('success', "Request {$restockRequest->request_number} approved successfully!");
    }
    
    /**
     * Reject restock request
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        $restockRequest = RestockRequest::findOrFail($id);
        
        if (!$restockRequest->canApprove()) {
            return back()->with('error', 'This request cannot be rejected. Current status: ' . $restockRequest->status);
        }
        
        $restockRequest->reject(auth()->id(), $validated['rejection_reason']);
        
        // Notify pharmacist
        \App\Models\StaffAlert::create([
            'sender_id' => auth()->id(),
            'sender_type' => 'admin',
            'recipient_id' => $restockRequest->requestedBy->user_id,
            'recipient_type' => 'pharmacist',
            'medicine_id' => $restockRequest->medicine_id,
            'alert_type' => 'Restock Rejected',
            'priority' => 'High',
            'alert_title' => 'Restock Request Rejected',
            'alert_message' => "Your restock request {$restockRequest->request_number} has been rejected. Reason: {$validated['rejection_reason']}",
            'action_url' => route('pharmacist.restock.show', $restockRequest->request_id),
        ]);
        
        return redirect()->route('admin.restock.index')
            ->with('success', "Request {$restockRequest->request_number} rejected.");
    }
    
    /**
     * Show all stock receipts (Admin view)
     */
    public function receiptsIndex(Request $request)
    {
        $query = StockReceipt::with(['medicine', 'receivedBy.user', 'restockRequest']);
        
        // Filter by quality status
        if ($request->filled('quality_status')) {
            $query->where('quality_status', $request->quality_status);
        }
        
        $receipts = $query->latest('received_at')->paginate(15);
        
        // Statistics
        $stats = [
            'total_received' => StockReceipt::whereDate('received_at', '>=', now()->subDays(30))->count(),
            'on_hold' => StockReceipt::where('quality_status', 'On Hold')->count(),
            'total_value' => StockReceipt::whereDate('received_at', '>=', now()->subDays(30))->sum('total_cost'),
        ];
        
        return view('admin.admin_restockReceipts', compact('receipts', 'stats'));
    }
    
    /**
     * Show receipt details
     */
    public function receiptShow($id)
    {
        $receipt = StockReceipt::with(['medicine', 'receivedBy.user', 'restockRequest'])
            ->findOrFail($id);
        
        return view('admin.admin_restockReceiptDetails', compact('receipt'));
    }
    
    /**
     * Show all disposals (Admin view)
     */
    public function disposalsIndex(Request $request)
    {
        $query = MedicineDisposal::with(['medicine', 'disposedBy.user', 'witnessedBy']);
        
        // Filter by reason
        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }
        
        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('disposed_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('disposed_at', '<=', $request->to_date);
        }
        
        $disposals = $query->latest('disposed_at')->paginate(15);
        
        // Statistics
        $stats = [
            'total_this_month' => MedicineDisposal::whereMonth('disposed_at', now()->month)->count(),
            'expired_count' => MedicineDisposal::where('reason', 'Expired')
                ->whereMonth('disposed_at', now()->month)->count(),
            'total_loss' => MedicineDisposal::whereMonth('disposed_at', now()->month)->sum('estimated_loss'),
        ];
        
        return view('admin.admin_restockDisposals', compact('disposals', 'stats'));
    }
    
    /**
     * Show disposal details
     */
    public function disposalShow($id)
    {
        $disposal = MedicineDisposal::with(['medicine', 'disposedBy.user', 'witnessedBy'])
            ->findOrFail($id);
        
        return view('admin.admin_restockDisposalDetails', compact('disposal'));
    }
    
    /**
     * Restock Reports & Analytics
     */
    public function reports()
    {
        // Monthly restock trend (last 6 months)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyData[] = [
                'month' => $month->format('M Y'),
                'requests' => RestockRequest::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)->count(),
                'approved' => RestockRequest::where('status', 'Approved')
                    ->whereMonth('approved_at', $month->month)
                    ->whereYear('approved_at', $month->year)->count(),
                'received_value' => StockReceipt::whereMonth('received_at', $month->month)
                    ->whereYear('received_at', $month->year)->sum('total_cost'),
            ];
        }
        
        // Top requested medicines
        $topMedicines = RestockRequest::with('medicine')
            ->selectRaw('medicine_id, COUNT(*) as request_count, SUM(quantity_requested) as total_qty')
            ->groupBy('medicine_id')
            ->orderByDesc('request_count')
            ->limit(10)
            ->get();
        
        // Disposal breakdown
        $disposalByReason = MedicineDisposal::selectRaw('reason, COUNT(*) as count, SUM(estimated_loss) as total_loss')
            ->groupBy('reason')
            ->get();
        
        return view('admin.admin_restockReports', compact('monthlyData', 'topMedicines', 'disposalByReason'));
    }
}