<?php
// app/Http/Controllers/Receptionist/ReceptionistAlertController.php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\StaffAlert;
use App\Models\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceptionistAlertController extends Controller
{
    /**
     * Display receptionist alerts and notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get filter from request (default: all)
        $filter = $request->get('filter', 'all');
        
        // Query staff alerts for receptionist
        // ✅ FIXED: Removed 'doctor' from with() - only load relationships that exist
        $alertsQuery = StaffAlert::with(['sender', 'patient', 'appointment'])
            ->where('recipient_id', $user->id)
            ->where('recipient_type', 'receptionist');
        
        // Apply filters
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
            case 'patient_updates':
                $alertsQuery->whereIn('alert_type', ['Patient Update', 'Patient Ready', 'Patient Checked In']);
                break;
        }
        
        // Get alerts with pagination
        $alerts = $alertsQuery->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Get counts for filter badges
        $counts = [
            'all' => StaffAlert::where('recipient_id', $user->id)
                ->where('recipient_type', 'receptionist')
                ->count(),
            'unread' => StaffAlert::where('recipient_id', $user->id)
                ->where('recipient_type', 'receptionist')
                ->where('is_read', false)
                ->count(),
            'urgent' => StaffAlert::where('recipient_id', $user->id)
                ->where('recipient_type', 'receptionist')
                ->whereIn('priority', ['Urgent', 'Critical'])
                ->where('is_read', false)
                ->count(),
            'cancellations' => StaffAlert::where('recipient_id', $user->id)
                ->where('recipient_type', 'receptionist')
                ->where('alert_type', 'Appointment Cancelled')
                ->where('is_read', false)
                ->count(),
        ];
        
        // Get recent system notifications
        $systemNotifications = SystemNotification::where('user_id', $user->id)
            ->where('user_role', 'receptionist')
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return view('receptionist.receptionist_alerts', compact(
            'alerts',
            'counts',
            'filter',
            'systemNotifications'
        ));
    }
    
    /**
     * Mark alert as read
     */
    public function markAsRead($id)
    {
        $alert = StaffAlert::findOrFail($id);
        
        // Verify ownership
        if ($alert->recipient_id !== Auth::id()) {
            abort(403);
        }
        
        $alert->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Alert marked as read'
        ]);
    }
    
    /**
     * Mark all alerts as read
     */
    public function markAllRead()
    {
        $user = Auth::user();
        
        StaffAlert::where('recipient_id', $user->id)
            ->where('recipient_type', 'receptionist')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        
        return response()->json([
            'success' => true,
            'message' => 'All alerts marked as read'
        ]);
    }
    
    /**
     * Delete alert
     */
    public function destroy($id)
    {
        $alert = StaffAlert::findOrFail($id);
        
        // Verify ownership
        if ($alert->recipient_id !== Auth::id()) {
            abort(403);
        }
        
        $alert->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Alert deleted'
        ]);
    }
    
    /**
     * Get unread count (for navbar badge)
     */
    public function getUnreadCount()
    {
        $count = StaffAlert::where('recipient_id', Auth::id())
            ->where('recipient_type', 'receptionist')
            ->where('is_read', false)
            ->count();
        
        return response()->json(['count' => $count]);
    }
}