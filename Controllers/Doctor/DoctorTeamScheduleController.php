<?php
// app/Http/Controllers/Doctor/DoctorTeamScheduleController.php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaffShift;
use App\Models\LeaveRequest;
use App\Models\LeaveEntitlement;
use Carbon\Carbon;

class DoctorTeamScheduleController extends Controller
{
    public function index(Request $request)
    {
        $doctor = auth()->user()->doctor;
        
        $weekStart = $request->week_start 
            ? Carbon::parse($request->week_start)->startOfWeek()
            : Carbon::now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        // ========================================
        // Get assigned nurses
        // ========================================
        $assignedNurses = $doctor->assignedNurses()->with('user')->get();

        // ========================================
        // Get shifts for the week
        // ========================================
        $myShifts = StaffShift::where('user_id', auth()->id())
            ->where('staff_role', 'doctor')
            ->whereBetween('shift_date', [$weekStart, $weekEnd])
            ->orderBy('shift_date')
            ->with('template')
            ->get();

        // ========================================
        // ✅ NEW: Get leave balance from database
        // ========================================
        $entitlement = LeaveEntitlement::getForUser(auth()->id());
        $leaveBalance = $entitlement->getBalanceSummary();

        // ========================================
        // Get pending and recent leaves
        // ========================================
        $pendingLeaves = LeaveRequest::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->count();
            
        $recentLeaves = LeaveRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('doctor.doctor_teamScheduleView', compact(
            'assignedNurses',
            'myShifts',
            'weekStart',
            'weekEnd',
            'lecentLeaves',
            'pendingLeaves',
            'recentLeaves'
        ));
    }

    public function applyLeave(Request $request)
    {
        // ========================================
        // Validation
        // ========================================
        $request->validate([
            'leave_type' => 'required|in:annual,sick,emergency,unpaid',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_half_day' => 'nullable|in:0,1,2',
            'reason' => 'required|string|max:500',
        ]);

        // ========================================
        // Map form values to DB enum
        // ========================================
        $leaveTypeMap = [
            'annual' => 'Annual Leave',
            'sick' => 'Sick Leave',
            'emergency' => 'Emergency Leave',
            'unpaid' => 'Unpaid Leave',
        ];

        $dbLeaveType = $leaveTypeMap[$request->leave_type];

        // ========================================
        // Calculate days
        // ========================================
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        $days = $startDate->diffInDays($endDate) + 1;
        if ($request->is_half_day > 0) {
            $days = 0.5;
        }

        // ========================================
        // ✅ Check balance using entitlement model
        // ========================================
        $entitlement = LeaveEntitlement::getForUser(auth()->id());
        
        if (!$entitlement->hasSufficientBalance($dbLeaveType, $days)) {
            $remaining = $entitlement->getRemainingDays($dbLeaveType);
            return back()->with('error', 
                "Insufficient leave balance. You have {$remaining} days remaining.");
        }

        // ========================================
        // Check for overlapping leave requests
        // ========================================
        $hasOverlap = LeaveRequest::where('user_id', auth()->id())
            ->where('status', '!=', 'rejected')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            })
            ->exists();

        if ($hasOverlap) {
            return back()->with('error', 
                'You already have a leave request for these dates.');
        }

        // ========================================
        // Create leave request
        // ========================================
        LeaveRequest::create([
            'user_id' => auth()->id(),
            'staff_role' => 'doctor',
            'leave_type' => $dbLeaveType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days' => $days,
            'is_half_day' => $request->is_half_day ?? 0,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return back()->with('success', 
            'Leave request submitted successfully! Awaiting admin approval.');
    }
}