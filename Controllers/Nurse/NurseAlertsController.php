<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaffAlert; // ✅ Changed from NurseAlert
use Carbon\Carbon;

class NurseAlertsController extends Controller
{
    /**
     * Display all alerts for the current nurse
     */
    public function index(Request $request)
    {
        $nurse = auth()->user()->nurse;

        if (!$nurse) {
            abort(403, 'Nurse profile not found.');
        }

        $filter = $request->get('filter', 'all');

        // ✅ Base query using StaffAlert with nurse scoping
        $alertsQuery = StaffAlert::with(['patient.user', 'sender'])
            ->where('recipient_id', auth()->id())
            ->where('recipient_type', 'nurse');

        // Apply filters
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
            case 'requires_action':
                $alertsQuery->whereNotNull('action_url')
                    ->where('is_acknowledged', false);
                break;
            case 'today':
                $alertsQuery->whereDate('created_at', today());
                break;
            case 'acknowledged':
                $alertsQuery->where('is_acknowledged', true);
                break;
        }

        // Paginate
        $alerts = $alertsQuery->orderByRaw("
                CASE priority
                    WHEN 'Critical' THEN 1
                    WHEN 'Urgent' THEN 2
                    WHEN 'High' THEN 3
                    WHEN 'Normal' THEN 4
                END
            ")
            ->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // ✅ Calculate counts using StaffAlert
        $counts = [
            'total' => StaffAlert::where('recipient_id', auth()->id())
                ->where('recipient_type', 'nurse')->count(),
            'all' => StaffAlert::where('recipient_id', auth()->id())
                ->where('recipient_type', 'nurse')->count(),
            'unread' => StaffAlert::where('recipient_id', auth()->id())
                ->where('recipient_type', 'nurse')
                ->where('is_read', false)->count(),
            'critical' => StaffAlert::where('recipient_id', auth()->id())
                ->where('recipient_type', 'nurse')
                ->where('priority', 'Critical')
                ->where('is_acknowledged', false)->count(),
            'urgent' => StaffAlert::where('recipient_id', auth()->id())
                ->where('recipient_type', 'nurse')
                ->whereIn('priority', ['Urgent', 'Critical'])
                ->where('is_acknowledged', false)->count(),
            'requires_action' => StaffAlert::where('recipient_id', auth()->id())
                ->where('recipient_type', 'nurse')
                ->whereNotNull('action_url')
                ->where('is_acknowledged', false)->count(),
            'today' => StaffAlert::where('recipient_id', auth()->id())
                ->where('recipient_type', 'nurse')
                ->whereDate('created_at', today())->count(),
        ];

        return view('nurse.nurse_alerts', compact('alerts', 'filter', 'counts'));
    }

    /**
     * Mark alert as read
     */
    public function markAsRead($id)
    {
        $alert = StaffAlert::findOrFail($id);

        // Verify alert belongs to current user
        if ($alert->recipient_id !== auth()->id() || $alert->recipient_type !== 'nurse') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $alert->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Alert marked as read.');
    }

    /**
     * Acknowledge alert
     */
    public function acknowledge($id)
    {
        $alert = StaffAlert::findOrFail($id);

        // Verify alert belongs to current user
        if ($alert->recipient_id !== auth()->id() || $alert->recipient_type !== 'nurse') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $alert->update([
            'is_acknowledged' => true,
            'acknowledged_at' => now(),
            'is_read' => true,
            'read_at' => $alert->read_at ?? now()
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Alert acknowledged.');
    }

    /**
     * Mark all alerts as read
     */
    public function markAllRead()
    {
        StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'nurse')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return redirect()->back()->with('success', 'All alerts marked as read.');
    }
}