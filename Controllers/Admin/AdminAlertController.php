<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffAlert;
use Illuminate\Http\Request;

class AdminAlertController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $search = $request->get('search');

        $query = StaffAlert::with(['sender', 'patient.user', 'medicine', 'appointment'])
            ->where('recipient_id', auth()->id())
            ->where('recipient_type', 'admin');

        switch ($filter) {
            case 'unread':
                $query->where('is_read', false);
                break;
            case 'critical':
                $query->where('priority', 'Critical')->where('is_acknowledged', false);
                break;
            case 'urgent':
                $query->whereIn('priority', ['Critical', 'Urgent'])->where('is_acknowledged', false);
                break;
            case 'pending':
                $query->where('is_acknowledged', false);
                break;
            case 'acknowledged':
                $query->where('is_acknowledged', true);
                break;
            case 'today':
                $query->whereDate('created_at', today());
                break;
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('alert_title', 'like', "%{$search}%")
                  ->orWhere('alert_message', 'like', "%{$search}%")
                  ->orWhereHas('sender', fn ($sq) =>
                      $sq->where('name', 'like', "%{$search}%")
                  );
            });
        }

        $alerts = $query
            ->orderByRaw("CASE priority
                WHEN 'Critical' THEN 1
                WHEN 'Urgent'   THEN 2
                WHEN 'High'     THEN 3
                WHEN 'Normal'   THEN 4
            END")
            ->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $raw = StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'admin')
            ->selectRaw("
                COUNT(*) as total,
                SUM(is_read = 0) as unread,
                SUM(priority = 'Critical' AND is_acknowledged = 0) as critical,
                SUM(priority IN ('Critical','Urgent') AND is_acknowledged = 0) as urgent,
                SUM(is_acknowledged = 0) as pending,
                SUM(is_acknowledged = 1) as acknowledged,
                SUM(DATE(created_at) = CURDATE()) as today
            ")
            ->first();

        $counts = [
            'total'        => $raw->total        ?? 0,
            'unread'       => $raw->unread        ?? 0,
            'critical'     => $raw->critical      ?? 0,
            'urgent'       => $raw->urgent        ?? 0,
            'pending'      => $raw->pending       ?? 0,
            'acknowledged' => $raw->acknowledged  ?? 0,
            'today'        => $raw->today         ?? 0,
        ];

        return view('admin.admin_alerts', compact('alerts', 'filter', 'counts', 'search'));
    }

    public function markAsRead($id)
    {
        $alert = StaffAlert::findOrFail($id);

        if ($alert->recipient_id !== auth()->id() || $alert->recipient_type !== 'admin') {
            abort(403);
        }

        $alert->update(['is_read' => true, 'read_at' => now()]);

        return redirect()->back()->with('success', 'Alert marked as read.');
    }

    public function acknowledge($id)
    {
        $alert = StaffAlert::findOrFail($id);

        if ($alert->recipient_id !== auth()->id() || $alert->recipient_type !== 'admin') {
            abort(403);
        }

        $alert->update([
            'is_acknowledged' => true,
            'acknowledged_at' => now(),
            'is_read'         => true,
            'read_at'         => $alert->read_at ?? now(),
        ]);

        return redirect()->back()->with('success', 'Alert acknowledged.');
    }

    public function markAllRead()
    {
        StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return redirect()->back()->with('success', 'All alerts marked as read.');
    }

    public function destroy($id)
    {
        $alert = StaffAlert::findOrFail($id);

        if ($alert->recipient_id !== auth()->id()) {
            abort(403);
        }

        $alert->delete();

        return redirect()->back()->with('success', 'Alert deleted.');
    }

    public function unreadCount()
    {
        $raw = StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'admin')
            ->selectRaw("
                SUM(is_read = 0) as unread,
                SUM(priority = 'Critical' AND is_acknowledged = 0) as critical
            ")
            ->first();

        return response()->json([
            'count'    => (int) ($raw->unread   ?? 0),
            'critical' => (int) ($raw->critical ?? 0),
        ]);
    }
}