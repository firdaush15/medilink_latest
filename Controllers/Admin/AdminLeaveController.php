<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\User;

class AdminLeaveController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        
        $leaves = LeaveRequest::with(['user', 'approvedBy'])
            ->when($status !== 'all', function($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($request->search, function($query, $search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->when($request->role, function($query, $role) {
                $query->where('staff_role', $role);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.admin_LeaveManagement', compact('leaves', 'stats', 'status'));
    }

    public function show($id)
    {
        $leave = LeaveRequest::with(['user', 'approvedBy'])->findOrFail($id);
        return view('admin.leaves.show', compact('leave'));
    }

    public function approve($id)
    {
        $leave = LeaveRequest::findOrFail($id);
        
        if ($leave->status !== 'pending') {
            return back()->with('error', 'This leave request has already been processed.');
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Leave request approved successfully!');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $leave = LeaveRequest::findOrFail($id);
        
        if ($leave->status !== 'pending') {
            return back()->with('error', 'This leave request has already been processed.');
        }

        $leave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Leave request rejected.');
    }
}