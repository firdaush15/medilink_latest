<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\StaffAlert;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceptionistAlertController extends Controller
{
    public function index(Request $request)
    {
        $user   = Auth::user();
        $filter = $request->get('filter', 'all');

        $alertsQuery = StaffAlert::with(['sender', 'patient', 'appointment'])
            ->where('recipient_id', $user->id)
            ->where('recipient_type', 'receptionist');

        switch ($filter) {
            case 'unread':
                $alertsQuery->where('is_read', false);
                break;
            case 'urgent':
                $alertsQuery->whereIn('priority', ['Urgent', 'Critical']);
                break;
            case 'cancellations':
                $alertsQuery->where('alert_type', 'Appointment Cancelled');
                break;
            case 'checkout':
                $alertsQuery->where('alert_type', 'Ready for Checkout');
                break;
            case 'patient_updates':
                $alertsQuery->whereIn('alert_type', ['Patient Update', 'Patient Ready', 'Patient Checked In']);
                break;
        }

        $alerts = $alertsQuery->orderBy('created_at', 'desc')->paginate(20);

        $rawCounts = StaffAlert::where('recipient_id', $user->id)
            ->where('recipient_type', 'receptionist')
            ->selectRaw("
                COUNT(*) as total,
                SUM(is_read = 0) as unread,
                SUM(priority IN ('Urgent','Critical') AND is_read = 0) as urgent,
                SUM(alert_type = 'Appointment Cancelled') as cancellations,
                SUM(alert_type = 'Ready for Checkout') as checkout
            ")
            ->first();

        $counts = [
            'all'          => $rawCounts->total         ?? 0,
            'unread'       => $rawCounts->unread         ?? 0,
            'urgent'       => $rawCounts->urgent         ?? 0,
            'cancellations'=> $rawCounts->cancellations  ?? 0,
            'checkout'     => $rawCounts->checkout       ?? 0,
        ];

        $systemNotifications = SystemNotification::where('user_id', $user->id)
            ->where('user_role', 'receptionist')
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Staff the receptionist can alert
        $doctors       = User::where('role', 'doctor')->orderBy('name')->get();
        $nurses        = User::where('role', 'nurse')->orderBy('name')->get();
        $adminUsers    = User::where('role', 'admin')->orderBy('name')->get();
        $pharmacists   = User::where('role', 'pharmacist')->orderBy('name')->get();

        return view('receptionist.receptionist_alerts', compact(
            'alerts', 'counts', 'filter', 'systemNotifications',
            'doctors', 'nurses', 'adminUsers', 'pharmacists'
        ));
    }

    /**
     * Receptionist sends alert to doctor, nurse, admin, or pharmacist.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient_id'   => 'required|exists:users,id',
            'recipient_type' => 'required|in:doctor,nurse,admin,pharmacist',
            'alert_type'     => 'required|string|max:100',
            'priority'       => 'required|in:Normal,High,Urgent,Critical',
            'alert_title'    => 'required|string|max:255',
            'alert_message'  => 'required|string|max:1000',
            'patient_id'     => 'nullable|exists:patients,patient_id',
        ]);

        // Verify recipient has the claimed role
        $recipient = User::findOrFail($validated['recipient_id']);
        if ($recipient->role !== $validated['recipient_type']) {
            return redirect()->back()->withErrors(['recipient_id' => 'Recipient role mismatch.']);
        }

        StaffAlert::create([
            'sender_id'      => auth()->id(),
            'sender_type'    => 'receptionist',
            'recipient_id'   => $validated['recipient_id'],
            'recipient_type' => $validated['recipient_type'],
            'patient_id'     => $validated['patient_id'] ?? null,
            'alert_type'     => $validated['alert_type'],
            'priority'       => $validated['priority'],
            'alert_title'    => $validated['alert_title'],
            'alert_message'  => $validated['alert_message'],
        ]);

        return redirect()->back()->with('success', 'Alert sent successfully.');
    }

    public function markAsRead($id)
    {
        $alert = StaffAlert::findOrFail($id);

        if ($alert->recipient_id !== Auth::id()) {
            abort(403);
        }

        $alert->markAsRead();

        return response()->json(['success' => true, 'message' => 'Alert marked as read']);
    }

    public function markAllRead()
    {
        $user = Auth::user();

        StaffAlert::where('recipient_id', $user->id)
            ->where('recipient_type', 'receptionist')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true, 'message' => 'All alerts marked as read']);
    }

    public function destroy($id)
    {
        $alert = StaffAlert::findOrFail($id);

        if ($alert->recipient_id !== Auth::id()) {
            abort(403);
        }

        $alert->delete();

        return response()->json(['success' => true, 'message' => 'Alert deleted']);
    }

    public function getUnreadCount()
    {
        $count = StaffAlert::where('recipient_id', Auth::id())
            ->where('recipient_type', 'receptionist')
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}