<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\StaffAlert;
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

        // ✅ Server-side filtering (replaces client-side JS filtering)
        $alertsQuery = StaffAlert::with(['medicine', 'prescription', 'patient.user', 'sender'])
            ->where('recipient_id', auth()->id())
            ->where('recipient_type', 'pharmacist');

        switch ($filter) {
            case 'unread':
                $alertsQuery->where('is_read', false);
                break;
            case 'critical':
                $alertsQuery->where('priority', 'Critical')
                            ->where('is_acknowledged', false);
                break;
            case 'urgent':
                $alertsQuery->whereIn('priority', ['Urgent', 'Critical'])
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
            case 'expired':
                $alertsQuery->where('alert_type', 'Expired Medicine');
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

        // ✅ Single aggregated count query (no N+1)
        $rawCounts = StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'pharmacist')
            ->selectRaw("
                COUNT(*) as total,
                SUM(is_read = 0) as unread,
                SUM(priority = 'Critical' AND is_acknowledged = 0) as critical,
                SUM(priority IN ('Urgent','Critical') AND is_acknowledged = 0) as urgent,
                SUM(is_acknowledged = 0) as pending,
                SUM(is_acknowledged = 1) as resolved,
                SUM(alert_type = 'Low Stock') as low_stock,
                SUM(alert_type = 'Expiring Soon') as expiring,
                SUM(alert_type = 'Expired Medicine') as expired,
                SUM(DATE(created_at) = CURDATE()) as today
            ")
            ->first();

        $counts = [
            'total'    => $rawCounts->total    ?? 0,
            'unread'   => $rawCounts->unread   ?? 0,
            'critical' => $rawCounts->critical ?? 0,
            'urgent'   => $rawCounts->urgent   ?? 0,
            'pending'  => $rawCounts->pending  ?? 0,
            'resolved' => $rawCounts->resolved ?? 0,
            'low_stock' => $rawCounts->low_stock ?? 0,
            'expiring' => $rawCounts->expiring ?? 0,
            'expired'  => $rawCounts->expired  ?? 0,
            'today'    => $rawCounts->today    ?? 0,
        ];

        return view('pharmacist.pharmacist_alerts', compact('alerts', 'filter', 'counts'));
    }

    /**
     * ✅ NEW: Soft-poll endpoint for navbar badge update.
     */
    public function getUnreadCount()
    {
        $rawCounts = StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'pharmacist')
            ->selectRaw("
                SUM(is_read = 0) as unread,
                SUM(priority = 'Critical' AND is_acknowledged = 0) as critical
            ")
            ->first();

        return response()->json([
            'count'    => (int) ($rawCounts->unread   ?? 0),
            'critical' => (int) ($rawCounts->critical ?? 0),
        ]);
    }

    public function markAsRead($id)
    {
        $alert = StaffAlert::findOrFail($id);

        if ($alert->recipient_id !== auth()->id() || $alert->recipient_type !== 'pharmacist') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $alert->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Alert marked as read.');
    }

    /**
     * ✅ RENAMED from resolve() to acknowledge() for consistency with other roles.
     * The route in web.php still points here — just rename the method.
     */
    public function acknowledge($id)
    {
        $alert = StaffAlert::findOrFail($id);

        if ($alert->recipient_id !== auth()->id() || $alert->recipient_type !== 'pharmacist') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $alert->update([
            'is_acknowledged' => true,
            'acknowledged_at' => now(),
            'is_read'         => true,
            'read_at'         => $alert->read_at ?? now(),
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Alert resolved.');
    }

    public function markAllRead()
    {
        StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'pharmacist')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return redirect()->back()->with('success', 'All alerts marked as read.');
    }
}