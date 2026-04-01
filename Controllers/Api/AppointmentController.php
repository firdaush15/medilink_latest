<?php
// app/Http/Controllers/Api/AppointmentController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * ✅ UPDATED: Get available doctors with workload information
     */
    public function getDoctors()
    {
        try {
            $today = Carbon::today();

            $doctors = Doctor::with('user')
                ->where('availability_status', 'Available')
                ->get()
                ->map(function ($doctor) use ($today) {
                    // Count total active appointments
                    $totalAppointments = Appointment::where('doctor_id', $doctor->doctor_id)
                        ->whereIn('status', ['confirmed', 'checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
                        ->count();

                    // Count today's appointments
                    $todayAppointments = Appointment::where('doctor_id', $doctor->doctor_id)
                        ->whereDate('appointment_date', $today)
                        ->whereIn('status', ['confirmed', 'checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
                        ->count();

                    return [
                        'doctor_id' => $doctor->doctor_id,
                        'name' => $doctor->user->name,
                        'specialization' => $doctor->specialization,
                        'profile_photo' => $doctor->profile_photo,
                        'appointment_count' => $totalAppointments,  // ✅ Total workload
                        'today_count' => $todayAppointments,        // ✅ Today's workload
                        'availability' => $doctor->availability_status,
                    ];
                });

            return response()->json([
                'success' => true,
                'doctors' => $doctors
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch doctors',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available time slots for a specific doctor and date
     */
    public function getAvailableTimeSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $doctorId = $request->doctor_id;
            $date = Carbon::parse($request->date);

            // Get existing appointments for this doctor on this date
            $existingAppointments = Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $date)
                ->whereIn('status', ['confirmed', 'checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
                ->pluck('appointment_time')
                ->map(function ($time) {
                    return Carbon::parse($time)->format('H:i');
                })
                ->toArray();

            // Generate time slots (9 AM to 5 PM, 30-minute intervals)
            $startTime = Carbon::parse('09:00');
            $endTime = Carbon::parse('17:00');
            $availableSlots = [];

            while ($startTime < $endTime) {
                $timeSlot = $startTime->format('H:i');
                
                if (!in_array($timeSlot, $existingAppointments)) {
                    $availableSlots[] = [
                        'time' => $timeSlot,
                        'display' => $startTime->format('h:i A'),
                    ];
                }
                
                $startTime->addMinutes(30);
            }

            return response()->json([
                'success' => true,
                'available_slots' => $availableSlots
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch time slots',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Book a new appointment
     */
    public function bookAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'appointment_type' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get patient_id from user_id
            $patient = Patient::where('user_id', $request->user_id)->first();
            
            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient record not found'
                ], 404);
            }

            // Check if time slot is still available
            $existingAppointment = Appointment::where('doctor_id', $request->doctor_id)
                ->whereDate('appointment_date', $request->appointment_date)
                ->whereTime('appointment_time', $request->appointment_time)
                ->whereIn('status', ['confirmed', 'checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
                ->exists();

            if ($existingAppointment) {
                return response()->json([
                    'success' => false,
                    'message' => 'This time slot is no longer available. Please choose another time.'
                ], 409);
            }

            // Create appointment
            $appointment = Appointment::create([
                'patient_id' => $patient->patient_id,
                'doctor_id' => $request->doctor_id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'status' => 'confirmed',
                'reason' => $request->reason,
            ]);

            // Load relationships for response
            $appointment->load(['patient.user', 'doctor.user']);

            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully!',
                'appointment' => [
                    'appointment_id' => $appointment->appointment_id,
                    'doctor_name' => $appointment->doctor->user->name,
                    'doctor_specialization' => $appointment->doctor->specialization,
                    'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                    'appointment_time' => Carbon::parse($appointment->appointment_time)->format('h:i A'),
                    'appointment_type' => $request->appointment_type,
                    'reason' => $appointment->reason,
                    'status' => $appointment->status,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to book appointment',
                'error' => $e->getMessage()
            ], 500);
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
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $patient = Patient::where('user_id', $request->user_id)->first();
            
            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient record not found'
                ], 404);
            }

            $appointments = Appointment::with(['doctor.user'])
                ->where('patient_id', $patient->patient_id)
                ->whereIn('status', ['confirmed', 'checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->get()
                ->map(function ($appointment) {
                    return [
                        'appointment_id' => $appointment->appointment_id,
                        'doctor_name' => $appointment->doctor->user->name,
                        'doctor_specialization' => $appointment->doctor->specialization,
                        'appointment_date' => $appointment->appointment_date->format('d M Y'),
                        'appointment_time' => Carbon::parse($appointment->appointment_time)->format('h:i A'),
                        'reason' => $appointment->reason,
                        'status' => $appointment->status,
                        'status_display' => $appointment->getCurrentStageDisplay(),
                    ];
                });

            return response()->json([
                'success' => true,
                'appointments' => $appointments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch appointments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel appointment
     */
    public function cancelAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|exists:appointments,appointment_id',
            'cancelled_reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $appointment = Appointment::find($request->appointment_id);

            if ($appointment->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Appointment is already cancelled'
                ], 400);
            }

            $appointment->update([
                'status' => 'cancelled',
                'cancelled_reason' => $request->cancelled_reason ?? 'Cancelled by patient',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment cancelled successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}