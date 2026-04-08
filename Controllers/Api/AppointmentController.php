<?php
// app/Http/Controllers/Api/AppointmentController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\StaffAlert;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Get available doctors with workload information
     */
    public function getDoctors()
    {
        try {
            $today = Carbon::today();

            $doctors = Doctor::with('user')
                ->where('availability_status', 'Available')
                ->get()
                ->map(function ($doctor) use ($today) {
                    $totalAppointments = Appointment::where('doctor_id', $doctor->doctor_id)
                        ->whereIn('status', ['confirmed', 'checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
                        ->count();

                    $todayAppointments = Appointment::where('doctor_id', $doctor->doctor_id)
                        ->whereDate('appointment_date', $today)
                        ->whereIn('status', ['confirmed', 'checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
                        ->count();

                    return [
                        'doctor_id'        => $doctor->doctor_id,
                        'name'             => $doctor->user->name,
                        'specialization'   => $doctor->specialization,
                        'profile_photo'    => $doctor->profile_photo,
                        'appointment_count'=> $totalAppointments,
                        'today_count'      => $todayAppointments,
                        'availability'     => $doctor->availability_status,
                    ];
                });

            return response()->json(['success' => true, 'doctors' => $doctors], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch doctors', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get available time slots for a specific doctor and date
     */
    public function getAvailableTimeSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'date'      => 'required|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        try {
            $doctorId = $request->doctor_id;
            $date     = Carbon::parse($request->date);

            $existingAppointments = Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $date)
                ->whereIn('status', ['confirmed', 'checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
                ->pluck('appointment_time')
                ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
                ->toArray();

            $startTime      = Carbon::parse('09:00');
            $endTime        = Carbon::parse('17:00');
            $availableSlots = [];

            while ($startTime < $endTime) {
                $timeSlot = $startTime->format('H:i');
                if (!in_array($timeSlot, $existingAppointments)) {
                    $availableSlots[] = ['time' => $timeSlot, 'display' => $startTime->format('h:i A')];
                }
                $startTime->addMinutes(30);
            }

            return response()->json(['success' => true, 'available_slots' => $availableSlots], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch time slots', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Book a new appointment
     */
    public function bookAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'          => 'required|exists:users,id',
            'doctor_id'        => 'required|exists:doctors,doctor_id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'appointment_type' => 'required|string',
            'reason'           => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        try {
            $patient = Patient::where('user_id', $request->user_id)->first();

            if (!$patient) {
                return response()->json(['success' => false, 'message' => 'Patient record not found'], 404);
            }

            $existingAppointment = Appointment::where('doctor_id', $request->doctor_id)
                ->whereDate('appointment_date', $request->appointment_date)
                ->whereTime('appointment_time', $request->appointment_time)
                ->whereIn('status', ['confirmed', 'checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
                ->exists();

            if ($existingAppointment) {
                return response()->json(['success' => false, 'message' => 'This time slot is no longer available. Please choose another time.'], 409);
            }

            $appointment = Appointment::create([
                'patient_id'       => $patient->patient_id,
                'doctor_id'        => $request->doctor_id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'status'           => 'confirmed',
                'reason'           => $request->reason,
            ]);

            $appointment->load(['patient.user', 'doctor.user']);

            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully!',
                'appointment' => [
                    'appointment_id'        => $appointment->appointment_id,
                    'doctor_name'           => $appointment->doctor->user->name,
                    'doctor_specialization' => $appointment->doctor->specialization,
                    'appointment_date'      => $appointment->appointment_date->format('Y-m-d'),
                    'appointment_time'      => Carbon::parse($appointment->appointment_time)->format('h:i A'),
                    'appointment_type'      => $request->appointment_type,
                    'reason'                => $appointment->reason,
                    'status'                => $appointment->status,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to book appointment', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get patient's appointments
     */
    public function getPatientAppointments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        try {
            $patient = Patient::where('user_id', $request->user_id)->first();

            if (!$patient) {
                return response()->json(['success' => false, 'message' => 'Patient record not found'], 404);
            }

            $appointments = Appointment::with(['doctor.user'])
                ->where('patient_id', $patient->patient_id)
                ->whereIn('status', ['confirmed', 'checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->get()
                ->map(function ($appointment) {
                    return [
                        'appointment_id'        => $appointment->appointment_id,
                        'doctor_name'           => $appointment->doctor->user->name,
                        'doctor_specialization' => $appointment->doctor->specialization,
                        'appointment_date'      => $appointment->appointment_date->format('d M Y'),
                        'appointment_time'      => Carbon::parse($appointment->appointment_time)->format('h:i A'),
                        'reason'                => $appointment->reason,
                        'status'                => $appointment->status,
                        'status_display'        => $appointment->getCurrentStageDisplay(),
                    ];
                });

            return response()->json(['success' => true, 'appointments' => $appointments], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch appointments', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel appointment (called by patient from mobile app).
     *
     * ✅ Alerts:
     *   - Doctor: always — they need to know their slot is now free.
     *   - Receptionist(s): always — they manage the schedule at the front desk.
     *   - Nurse: only if the patient had already checked in and was assigned a nurse
     *            (same edge-case guard as check-in: arrived_at + assigned_nurse_id must be set).
     */
    public function cancelAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id'   => 'required|exists:appointments,appointment_id',
            'cancelled_reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        try {
            $appointment = Appointment::with([
                'doctor.user',
                'patient.user',
                'assignedNurse.user',
            ])->find($request->appointment_id);

            if ($appointment->status === 'cancelled') {
                return response()->json(['success' => false, 'message' => 'Appointment is already cancelled'], 400);
            }

            $cancelReason = $request->cancelled_reason ?? 'Cancelled by patient';

            $appointment->update([
                'status'           => 'cancelled',
                'cancelled_reason' => $cancelReason,
            ]);

            $patientName     = $appointment->patient->user->name;
            $appointmentDate = $appointment->appointment_date->format('d M Y');
            $appointmentTime = Carbon::parse($appointment->appointment_time)->format('h:i A');

            // ── Alert 1: Doctor ──────────────────────────────────────────────────
            // Always fire. The doctor's slot is now free and they need to know.
            StaffAlert::create([
                'sender_id'      => $appointment->patient->user_id,
                'sender_type'    => 'system',
                'recipient_id'   => $appointment->doctor->user_id,
                'recipient_type' => 'doctor',
                'patient_id'     => $appointment->patient_id,
                'appointment_id' => $appointment->appointment_id,
                'alert_type'     => 'Appointment Cancelled',
                'priority'       => 'High',
                'alert_title'    => '❌ Appointment Cancelled by Patient',
                'alert_message'  => "{$patientName} has cancelled their appointment on {$appointmentDate} "
                                  . "at {$appointmentTime}. Reason: {$cancelReason}",
                'action_url'     => route('doctor.appointments'),
            ]);

            // ── Alert 2: All receptionists ───────────────────────────────────────
            // Always fire. Receptionists manage the front-desk schedule and need to
            // know the slot opened up so they can update the queue display and
            // potentially book another patient into that slot.
            $receptionists = User::where('role', 'receptionist')->get();

            foreach ($receptionists as $receptionist) {
                StaffAlert::create([
                    'sender_id'      => $appointment->patient->user_id,
                    'sender_type'    => 'system',
                    'recipient_id'   => $receptionist->id,
                    'recipient_type' => 'receptionist',
                    'patient_id'     => $appointment->patient_id,
                    'appointment_id' => $appointment->appointment_id,
                    'alert_type'     => 'Appointment Cancelled',
                    'priority'       => 'Normal',
                    'alert_title'    => '❌ Appointment Cancelled by Patient',
                    'alert_message'  => "{$patientName} has cancelled their appointment with "
                                      . "Dr. {$appointment->doctor->user->name} on {$appointmentDate} "
                                      . "at {$appointmentTime}.",
                    'action_url'     => route('receptionist.appointments'),
                ]);
            }

            // ── Alert 3: Assigned nurse (only if patient already checked in) ─────
            // Nurse is assigned during check-in, so arrived_at will only be set
            // if the patient physically arrived. If the patient cancels before
            // arriving, no nurse was ever assigned — so no alert needed.
            if (
                $appointment->arrived_at !== null &&
                $appointment->assigned_nurse_id !== null &&
                $appointment->assignedNurse !== null
            ) {
                StaffAlert::create([
                    'sender_id'      => $appointment->patient->user_id,
                    'sender_type'    => 'system',
                    'recipient_id'   => $appointment->assignedNurse->user_id,
                    'recipient_type' => 'nurse',
                    'patient_id'     => $appointment->patient_id,
                    'appointment_id' => $appointment->appointment_id,
                    'alert_type'     => 'Appointment Cancelled',
                    'priority'       => 'High',
                    'alert_title'    => '❌ Appointment Cancelled — Remove Patient from Queue',
                    'alert_message'  => "{$patientName}'s appointment was cancelled after check-in. "
                                      . "Please stop any ongoing vitals recording and remove them from your queue.",
                    'action_url'     => route('nurse.patients'),
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Appointment cancelled successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to cancel appointment', 'error' => $e->getMessage()], 500);
        }
    }
}