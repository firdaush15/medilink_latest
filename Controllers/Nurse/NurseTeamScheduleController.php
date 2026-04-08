<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaffShift;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class NurseTeamScheduleController extends Controller
{
    public function index(Request $request)
    {
        $nurse = auth()->user()->nurse;
        
        $weekStart = $request->week_start 
            ? Carbon::parse($request->week_start)->startOfWeek()
            : Carbon::now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $assignedDoctors = $nurse->assignedDoctors()->with('user')->get();

        $myShifts = StaffShift::where('user_id', auth()->id())
            ->where('staff_role', 'nurse')
            ->whereBetween('shift_date', [$weekStart, $weekEnd])
            ->orderBy('shift_date')
            ->with('template')
            ->get();

        $leaveBalance = [
            'annual' => 14 - LeaveRequest::where('user_id', auth()->id())
                ->where('leave_type', 'Annual Leave')
                ->where('status', 'approved')
                ->whereYear('start_date', date('Y'))
                ->sum('days'),
            'sick' => 14 - LeaveRequest::where('user_id', auth()->id())
                ->where('leave_type', 'Sick Leave')
                ->where('status', 'approved')
                ->whereYear('start_date', date('Y'))
                ->sum('days'),
            'used' => LeaveRequest::where('user_id', auth()->id())
                ->where('status', 'approved')
                ->whereYear('start_date', date('Y'))
                ->sum('days'),
        ];

        $pendingLeaves = LeaveRequest::where('user_id', auth()->id())->where('status', 'pending')->count();
        $recentLeaves = LeaveRequest::where('user_id', auth()->id())->orderBy('created_at', 'desc')->take(5)->get();

        return view('nurse.nurse_teamScheduleView', compact(
            'assignedDoctors', 'myShifts', 'weekStart', 'weekEnd',
            'leaveBalance', 'pendingLeaves', 'recentLeaves'
        ));
    }

    public function applyLeave(Request $request)
    {
        // ✅ FIX: Validate against lowercase values, then map to DB format
        $request->validate([
            'leave_type' => 'required|in:annual,sick,emergency,maternity,unpaid',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_half_day' => 'nullable|in:0,1,2',
            'reason' => 'required|string|max:500',
        ]);

        // ✅ Map form value to database enum value
        $leaveTypeMap = [
            'annual' => 'Annual Leave',
            'sick' => 'Sick Leave',
            'emergency' => 'Emergency Leave',
            'maternity' => 'Maternity Leave',
            'unpaid' => 'Unpaid Leave',
        ];

        $dbLeaveType = $leaveTypeMap[$request->leave_type];

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        $days = $startDate->diffInDays($endDate) + 1;
        if ($request->is_half_day > 0) {
            $days = 0.5;
        }

        if (!LeaveRequest::hasBalance(auth()->id(), $dbLeaveType, $days)) {
            return back()->with('error', 'Insufficient leave balance');
        }

        LeaveRequest::create([
            'user_id' => auth()->id(),
            'staff_role' => 'nurse',
            'leave_type' => $dbLeaveType, // ✅ Use mapped value
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days' => $days,
            'is_half_day' => $request->is_half_day ?? 0,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Leave request submitted successfully!');
    }
}