<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaffAlert;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

class NurseAlertsController extends Controller
{
    public function index(Request $request)
    {
        $nurse = auth()->user()->nurse;

        if (!$nurse) {
            abort(403, 'Nurse profile not found.');
        }

        $filter = $request->get('filter', 'all');

        $alertsQuery = StaffAlert::with(['patient.user', 'sender'])
            ->where('recipient_id', auth()->id())
            ->where('recipient_type', 'nurse');

        switch ($filter) {
            case 'unread':        $alertsQuery->where('is_read', false); break;
            case 'critical':      $alertsQuery->where('priority', 'Critical')->where('is_acknowledged', false); break;
            case 'urgent':        $alertsQuery->whereIn('priority', ['Urgent', 'Critical'])->where('is_acknowledged', false); break;
            case 'requires_action': $alertsQuery->whereNotNull('action_url')->where('is_acknowledged', false); break;
            case 'today':         $alertsQuery->whereDate('created_at', today()); break;
            case 'acknowledged':  $alertsQuery->where('is_acknowledged', true); break;
            case 'patient_assigned': $alertsQuery->where('alert_type', 'Patient Assigned'); break;
        }

        $alerts = $alertsQuery
            ->orderByRaw("
                CASE priority
                    WHEN 'Critical' THEN 1
                    WHEN 'Urgent'   THEN 2
                    WHEN 'High'     THEN 3
                    WHEN 'Normal'   THEN 4
                END
            ")
            ->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $rawCounts = StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'nurse')
            ->selectRaw("
                COUNT(*) as total,
                SUM(is_read = 0) as unread,
                SUM(priority = 'Critical' AND is_acknowledged = 0) as critical,
                SUM(priority IN ('Urgent','Critical') AND is_acknowledged = 0) as urgent,
                SUM(action_url IS NOT NULL AND is_acknowledged = 0) as requires_action,
                SUM(DATE(created_at) = CURDATE()) as today,
                SUM(alert_type = 'Patient Assigned') as patient_assigned
            ")
            ->first();

        $counts = [
            'total'            => $rawCounts->total            ?? 0,
            'all'              => $rawCounts->total            ?? 0,
            'unread'           => $rawCounts->unread           ?? 0,
            'critical'         => $rawCounts->critical         ?? 0,
            'urgent'           => $rawCounts->urgent           ?? 0,
            'requires_action'  => $rawCounts->requires_action  ?? 0,
            'today'            => $rawCounts->today            ?? 0,
            'patient_assigned' => $rawCounts->patient_assigned ?? 0,
        ];

        // Doctors the nurse can alert
        $doctors = Doctor::with('user')->get();

        // Admin users
        $adminUsers = User::where('role', 'admin')->orderBy('name')->get();

        // Recent patients for context dropdown
        $recentPatients = Patient::whereHas('appointments', function ($q) {
            $q->whereDate('appointment_date', today());
        })->with('user')->get();

        return view('nurse.nurse_alerts', compact(
            'alerts', 'filter', 'counts', 'doctors', 'recentPatients', 'adminUsers'
        ));
    }

    /**
     * Soft-poll endpoint — returns unread + critical counts.
     */
    public function getUnreadCount()
    {
        $rawCounts = StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'nurse')
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

    /**
     * ✅ Nurse sends an alert to a doctor.
     */
    public function sendToDoctor(Request $request)
    {
        $validated = $request->validate([
            'doctor_user_id' => 'required|exists:users,id',
            'patient_id'     => 'nullable|exists:patients,patient_id',
            'alert_type'     => 'required|string|max:100',
            'priority'       => 'required|in:Normal,High,Urgent,Critical',
            'alert_title'    => 'required|string|max:255',
            'alert_message'  => 'required|string|max:1000',
        ]);

        StaffAlert::create([
            'sender_id'      => auth()->id(),
            'sender_type'    => 'nurse',
            'recipient_id'   => $validated['doctor_user_id'],
            'recipient_type' => 'doctor',
            'patient_id'     => $validated['patient_id'] ?? null,
            'alert_type'     => $validated['alert_type'],
            'priority'       => $validated['priority'],
            'alert_title'    => $validated['alert_title'],
            'alert_message'  => $validated['alert_message'],
            'action_url'     => route('doctor.alerts.inbox'),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Alert sent to doctor.');
    }

    /**
     * ✅ NEW: Nurse sends an alert to admin
     */
    public function sendToAdmin(Request $request)
    {
        $validated = $request->validate([
            'admin_user_id'  => 'required|exists:users,id',
            'patient_id'     => 'nullable|exists:patients,patient_id',
            'alert_type'     => 'required|string|max:100',
            'priority'       => 'required|in:Normal,High,Urgent,Critical',
            'alert_title'    => 'required|string|max:255',
            'alert_message'  => 'required|string|max:1000',
        ]);

        // Verify the target user is actually an admin
        $adminUser = User::where('id', $validated['admin_user_id'])
            ->where('role', 'admin')
            ->firstOrFail();

        StaffAlert::create([
            'sender_id'      => auth()->id(),
            'sender_type'    => 'nurse',
            'recipient_id'   => $adminUser->id,
            'recipient_type' => 'admin',
            'patient_id'     => $validated['patient_id'] ?? null,
            'alert_type'     => $validated['alert_type'],
            'priority'       => $validated['priority'],
            'alert_title'    => $validated['alert_title'],
            'alert_message'  => $validated['alert_message'],
            'action_url'     => route('admin.alerts.index'),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Alert sent to admin.');
    }

    /**
     * Mark alert as read.
     */
    public function markAsRead($id)
    {
        $alert = StaffAlert::findOrFail($id);

        if ($alert->recipient_id !== auth()->id() || $alert->recipient_type !== 'nurse') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $alert->update(['is_read' => true, 'read_at' => now()]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Alert marked as read.');
    }

    /**
     * Acknowledge alert.
     */
    public function acknowledge($id)
    {
        $alert = StaffAlert::findOrFail($id);

        if ($alert->recipient_id !== auth()->id() || $alert->recipient_type !== 'nurse') {
            return response()->json(['error' => 'Unauthorized'], 403);
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

        return redirect()->back()->with('success', 'Alert acknowledged.');
    }

    /**
     * Mark all alerts as read.
     */
    public function markAllRead()
    {
        StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'nurse')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return redirect()->back()->with('success', 'All alerts marked as read.');
    }
}