<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReceptionistAppointmentController extends Controller
{
    public function index()
    {
        // ✅ FIXED: Removed 'checkIn' relationship (not used in current system)
        $appointments = Appointment::with([
            'patient.user', 
            'doctor.user',
            'receptionistWhoCheckedIn' // Use this instead of checkIn
        ])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(20);

        return view('receptionist.receptionist_appointments', compact('appointments'));
    }

    public function create(Request $request)
    {
        // Get patient if provided
        $selectedPatient = null;
        if ($request->has('patient_id')) {
            $selectedPatient = Patient::with('user')->find($request->patient_id);
        }

        // Get all patients for dropdown
        $patients = Patient::with('user')->get();

        // Get available doctors
        $doctors = Doctor::with('user')
            ->where('availability_status', 'Available')
            ->get();

        return view('receptionist.receptionist_appointmentCreate', compact('patients', 'doctors', 'selectedPatient'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,patient_id',
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            // Check for scheduling conflicts
            $conflict = Appointment::where('doctor_id', $validated['doctor_id'])
                ->where('appointment_date', $validated['appointment_date'])
                ->where('appointment_time', $validated['appointment_time'])
                ->whereIn('status', ['confirmed', 'completed'])
                ->exists();

            if ($conflict) {
                return back()
                    ->withInput()
                    ->withErrors(['appointment_time' => 'This time slot is already booked for the selected doctor.']);
            }

            // Check if doctor is on leave
            $doctor = Doctor::with(['leaves' => function($query) use ($validated) {
                $query->where('status', 'Approved')
                    ->where('start_date', '<=', $validated['appointment_date'])
                    ->where('end_date', '>=', $validated['appointment_date']);
            }])->find($validated['doctor_id']);

            if ($doctor->leaves->count() > 0) {
                return back()
                    ->withInput()
                    ->withErrors(['appointment_date' => 'The selected doctor is on leave on this date.']);
            }

            // Create appointment
            $appointment = Appointment::create([
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'],
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $validated['appointment_time'],
                'status' => 'confirmed',
                'reason' => $validated['reason'] ?? null,
                'arrival_status' => 'not_arrived', // ✅ Set initial status
            ]);

            return redirect()
                ->route('receptionist.appointments')
                ->with('success', 'Appointment scheduled successfully! Appointment ID: ' . $appointment->appointment_id);

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create appointment: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        // ✅ FIXED: Removed 'checkIn', added 'receptionistWhoCheckedIn'
        $appointment = Appointment::with([
            'patient.user',
            'doctor.user',
            'receptionistWhoCheckedIn',
            'prescriptions.items',
        ])->findOrFail($id);

        return view('receptionist.receptionist_appointmentDetails', compact('appointment'));
    }

    public function reschedule(Request $request, $id)
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'reschedule_reason' => 'nullable|string|max:500',
        ]);

        $appointment = Appointment::findOrFail($id);

        // ✅ FIXED: Check arrived_at instead of checkIn
        if ($appointment->arrived_at || $appointment->status === 'completed') {
            return back()->withErrors(['error' => 'Cannot reschedule a checked-in or completed appointment.']);
        }

        $conflict = Appointment::where('doctor_id', $appointment->doctor_id)
            ->where('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->where('appointment_id', '!=', $id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->exists();

        if ($conflict) {
            return back()->withErrors(['appointment_time' => 'This time slot is already booked.']);
        }

        try {
            $appointment->update([
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $validated['appointment_time'],
            ]);

            return back()->with('success', 'Appointment rescheduled successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Rescheduling failed: ' . $e->getMessage()]);
        }
    }

    public function cancel(Request $request, $id)
    {
        $validated = $request->validate([
            'cancelled_reason' => 'required|string|max:500',
        ]);

        $appointment = Appointment::findOrFail($id);

        if ($appointment->status === 'completed') {
            return back()->withErrors(['error' => 'Cannot cancel a completed appointment.']);
        }

        try {
            $appointment->update([
                'status' => 'cancelled',
                'cancelled_reason' => $validated['cancelled_reason'],
            ]);

            return back()->with('success', 'Appointment cancelled successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Cancellation failed: ' . $e->getMessage()]);
        }
    }
}