<?php
// ============================================
// 1. Admin\DoctorController.php
// ============================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\LeaveRequest;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $query = Doctor::with('user');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Specialization filter
        if ($request->filled('specialization')) {
            $query->where('specialization', $request->specialization);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('availability_status', $request->status);
        }

        // Stats
        $totalDoctors = $query->count();
        $availableDoctors = (clone $query)->where('availability_status', 'Available')->count();
        $unavailableDoctors = (clone $query)->where('availability_status', 'Unavailable')->count();
        $onLeaveDoctors = (clone $query)->where('availability_status', 'On Leave')->count();
        $newDoctorsThisWeek = (clone $query)->where('created_at', '>=', now()->subWeek())->count();

        $filteredDoctorUserIds = $query->pluck('user_id');
        $newLeavesThisWeek = LeaveRequest::whereIn('user_id', $filteredDoctorUserIds)
            ->where('staff_role', 'doctor')
            ->where('status', 'approved')
            ->where('start_date', '>=', now()->startOfWeek())
            ->count();

        $doctors = $query->paginate(10)->withQueryString();

        return view('admin.admin_manageDoctors', compact(
            'doctors',
            'totalDoctors',
            'availableDoctors',
            'unavailableDoctors',
            'onLeaveDoctors',
            'newDoctorsThisWeek',
            'newLeavesThisWeek'
        ));
    }

    /**
     * Show doctor profile
     */
    public function show($id)
    {
        $doctor = Doctor::with(['user', 'appointments', 'ratings'])->findOrFail($id);

        // Performance metrics
        $totalAppointments = $doctor->appointments()->count();
        $completedAppointments = $doctor->appointments()->where('status', 'completed')->count();
        $cancelledAppointments = $doctor->appointments()->where('status', 'cancelled')->count();
        $averageRating = $doctor->ratings()->avg('rating');
        $currentLeave = $doctor->currentLeave();

        return view('admin.doctors.show', compact(
            'doctor',
            'totalAppointments',
            'completedAppointments',
            'cancelledAppointments',
            'averageRating',
            'currentLeave'
        ));
    }

    /**
     * Show edit form (contact info, status only - NOT medical data)
     */
    public function edit($id)
    {
        $doctor = Doctor::with('user')->findOrFail($id);
        return view('admin.doctors.edit', compact('doctor'));
    }

    /**
     * Update doctor information
     */
    public function update(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);

        $validated = $request->validate([
            'phone_number' => 'required|string',
            'specialization' => 'required|string',
            'availability_status' => 'required|in:Available,On Leave,Unavailable',
        ]);

        $doctor->update($validated);

        // Log the change
        activity()
            ->performedOn($doctor)
            ->causedBy(auth()->user())
            ->withProperties(['attributes' => $validated])
            ->log('Admin updated doctor information');

        return redirect()->route('admin.doctors')->with('success', 'Doctor updated successfully');
    }

    /**
     * ✅ DEACTIVATE (NOT DELETE) - Real-world approach
     */
    public function deactivate($id)
    {
        $doctor = Doctor::findOrFail($id);

        $doctor->user->update([
            'is_active' => false,
            'deactivated_at' => now(),
            'deactivated_by' => auth()->id(),
        ]);

        $doctor->update([
            'availability_status' => 'Unavailable'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Doctor account deactivated (not deleted)'
        ]);
    }

    // ============================================
// Add this method to Admin\DoctorController.php
// ============================================

    /**
     * Show doctor schedule management page
     */
    public function schedule($id)
    {
        $doctor = Doctor::with(['user', 'assignedNurses'])->findOrFail($id);

        // Get shifts for this doctor
        $shifts = \App\Models\StaffShift::where('user_id', $doctor->user_id)
            ->where('staff_role', 'doctor')
            ->whereDate('shift_date', '>=', today())
            ->orderBy('shift_date')
            ->take(14) // Next 2 weeks
            ->get();

        // Get leave requests
        $leaves = \App\Models\LeaveRequest::where('user_id', $doctor->user_id)
            ->where('staff_role', 'doctor')
            ->whereDate('end_date', '>=', today())
            ->orderBy('start_date')
            ->get();

        return view('admin.doctors.schedule', compact('doctor', 'shifts', 'leaves'));
    }
}
