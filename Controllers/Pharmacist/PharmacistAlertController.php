<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\StaffAlert; // ✅ Changed from PharmacistAlert
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacistAlertController extends Controller
{
    public function index(Request $request)
    {
        $pharmacist = Auth::user()->pharmacist;

        if (!$pharmacist) {
            abort(403, 'Pharmacist profile not found.');
        }

        $filter = $request->get('filter', 'all');

        // ✅ Base query using StaffAlert with pharmacist scoping
        $alertsQuery = StaffAlert::with(['medicine', 'prescription', 'patient.user', 'sender'])
            ->where('recipient_id', auth()->id())
            ->where('recipient_type', 'pharmacist');

        // Apply filters
        switch ($filter) {
            case 'unread':
                $alertsQuery->where('is_read', false);
                break;
            case 'critical':
                $alertsQuery->where('priority', 'Critical')
                    ->where('is_acknowledged', false);
                break;
            case 'pending':
                $alertsQuery->where('is_acknowledged', false);
                break;
            case 'resolved':
                $alertsQuery->where('is_acknowledged', true);
                break;
            case 'low_stock':
                $alertsQuery->where('alert_type', 'Low Stock');
                break;
            case 'expiring':
                $alertsQuery->where('alert_type', 'Expiring Soon');
                break;
            case 'today':
                $alertsQuery->whereDate('created_at', today());
                break;
        }

        $alerts = $alertsQuery
            ->orderByRaw("FIELD(priority, 'Critical', 'Urgent', 'High', 'Normal')")
            ->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // ✅ Calculate counts using StaffAlert
        $counts = [
            'total' => StaffAlert::where('recipient_id', auth()->id())
                ->where('recipient_type', 'pharmacist')->count(),
            'unread' => StaffAlert::where('recipient_id', auth()->id())
                ->where('recipient_type', 'pharmacist')
                ->where('is_read', false)->count(),
            'critical' => StaffAlert::where('recipient_id', auth()->id())
                ->where('recipient_type', 'pharmacist')
                ->where('priority', 'Critical')
                ->where('is_acknowledged', false)->count(),
            'pending' => StaffAlert::where('recipient_id', auth()->id())
                ->where('recipient_type', 'pharmacist')
                ->where('is_acknowledged', false)->count(),
            'resolved' => StaffAlert::where('recipient_id', auth()->id())
                ->where('recipient_type', 'pharmacist')
                ->where('is_acknowledged', true)->count(),
        ];

        return view('pharmacist.pharmacist_alerts', compact('alerts', 'filter', 'counts'));
    }

    public function markAsRead($id)
    {
        $alert = StaffAlert::findOrFail($id);
        
        // Check if belongs to current pharmacist
        if ($alert->recipient_id !== auth()->id() || $alert->recipient_type !== 'pharmacist') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $alert->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    public function resolve($id)
    {
        $alert = StaffAlert::findOrFail($id);
        
        // Check if belongs to current pharmacist
        if ($alert->recipient_id !== auth()->id() || $alert->recipient_type !== 'pharmacist') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $alert->update([
            'is_acknowledged' => true,
            'acknowledged_at' => now(),
            'is_read' => true,
            'read_at' => $alert->read_at ?? now()
        ]);

        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'pharmacist')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return redirect()->back()->with('success', 'All alerts marked as read.');
    }
}