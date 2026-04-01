<?php

// ============================================
// 3. Admin\AppointmentController.php
// ============================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['doctor.user', 'patient.user']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('doctor.user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('patient.user', function ($q3) use ($search) {
                    $q3->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortOrder = $request->get('sort', 'desc');
        $query->orderBy('appointment_date', $sortOrder)
              ->orderBy('appointment_time', $sortOrder);

        $appointments = $query->paginate(10)->withQueryString();

        // Stats
        $todayCount = Appointment::whereDate('appointment_date', today())->count();
        $confirmCount = Appointment::where('status', 'confirmed')->count();
        $completedCount = Appointment::where('status', 'completed')->count();
        $cancelledCount = Appointment::where('status', 'cancelled')->count();

        return view('admin.admin_manageAppointments', compact(
            'appointments',
            'todayCount',
            'confirmCount',
            'completedCount',
            'cancelledCount'
        ));
    }

    /**
     * View appointment details
     */
    public function show($id)
    {
        $appointment = Appointment::with([
            'doctor.user',
            'patient.user',
            'workflowLogs',
            'vitals',
            'prescriptions'
        ])->findOrFail($id);

        return view('admin.appointments.show', compact('appointment'));
    }

    /**
     * Cancel appointment (with reason)
     */
    public function cancel(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $appointment->update([
            'status' => 'cancelled',
            'cancelled_reason' => $request->reason,
        ]);

        // Log workflow change
        $appointment->logWorkflowChange(
            $appointment->getOriginal('status'),
            'cancelled',
            auth()->id(),
            'admin',
            'Cancelled by admin: ' . $request->reason
        );

        return response()->json(['success' => true]);
    }

    /**
     * Mark appointment as no-show
     */
    public function markNoShow($id)
    {
        $appointment = Appointment::findOrFail($id);

        $appointment->update(['status' => 'no_show']);

        // Update patient no-show count
        $appointment->patient->increment('no_show_count');

        // Auto-flag patient if too many no-shows
        if ($appointment->patient->no_show_count >= 3) {
            $appointment->patient->update([
                'is_flagged' => true,
                'flag_reason' => 'Multiple no-shows (' . $appointment->patient->no_show_count . ')',
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Show reschedule form
     */
    public function reschedule($id)
    {
        $appointment = Appointment::with(['doctor', 'patient'])->findOrFail($id);
        $doctors = \App\Models\Doctor::where('availability_status', 'Available')->get();
        
        return view('admin.appointments.reschedule', compact('appointment', 'doctors'));
    }

    /**
     * Process reschedule
     */
    public function processReschedule(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $validated = $request->validate([
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'doctor_id' => 'required|exists:doctors,doctor_id',
        ]);

        $old = $appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time->format('H:i');
        
        $appointment->update($validated);

        $new = $validated['appointment_date'] . ' ' . $validated['appointment_time'];

        // Log change
        $appointment->logWorkflowChange(
            'rescheduled',
            'confirmed',
            auth()->id(),
            'admin',
            "Rescheduled from {$old} to {$new}"
        );

        return redirect()->route('admin.appointments')->with('success', 'Appointment rescheduled');
    }
}