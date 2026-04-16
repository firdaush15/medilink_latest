<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\PatientNurseAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NurseQueueController extends Controller
{
    /**
     * Display queue - ONLY show patients assigned to current nurse
     */
    public function index()
    {
        $nurse = auth()->user()->nurse;

        if (!$nurse) {
            abort(403, 'Nurse profile not found.');
        }

        Log::info("=== NURSE QUEUE PAGE LOADED ===");
        Log::info("Nurse ID: {$nurse->nurse_id}");
        Log::info("Nurse Name: {$nurse->user->name}");

        // ✅ IMPROVED: Get MY assigned patients with better logging
        $myAssignedAppointments = Appointment::with(['patient.user', 'doctor.user'])
            ->whereDate('appointment_date', today())
            ->where('assigned_nurse_id', $nurse->nurse_id)
            ->whereIn('status', [
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
            ])
            ->orderBy('queue_priority_score', 'asc')
            ->get();

        Log::info("Total assigned appointments found: {$myAssignedAppointments->count()}");

        foreach ($myAssignedAppointments as $appt) {
            Log::info("  - Patient: {$appt->patient->user->name} | Status: {$appt->status} | Queue: {$appt->queue_number}");
        }

        $nextPatient = $myAssignedAppointments->first();
        $upcomingPatients = $myAssignedAppointments->skip(1)->take(10);

        if ($nextPatient) {
            Log::info("Next patient to call: {$nextPatient->patient->user->name}");
        } else {
            Log::warning("No patients in queue!");
            
            // ✅ DEBUG: Check if ANY appointments exist today
            $allTodayAppointments = Appointment::whereDate('appointment_date', today())
                ->where('assigned_nurse_id', $nurse->nurse_id)
                ->get();
            
            Log::info("Total appointments assigned to this nurse today (all statuses): {$allTodayAppointments->count()}");
            
            foreach ($allTodayAppointments as $appt) {
                Log::info("  - Patient: {$appt->patient->user->name} | Status: {$appt->status}");
            }
        }
        
        return view('nurse.nurse_queueManagement', compact(
            'nextPatient', 
            'upcomingPatients',
            'nurse'
        ));
    }

    /**
     * Call patient
     */
    public function callPatient($appointmentId)
    {
        $nurse = auth()->user()->nurse;
        $appointment = Appointment::with(['patient.user', 'assignedNurse'])
            ->findOrFail($appointmentId);

        Log::info("=== CALLING PATIENT ===");
        Log::info("Appointment ID: {$appointmentId}");
        Log::info("Patient: {$appointment->patient->user->name}");
        Log::info("Assigned Nurse ID: {$appointment->assigned_nurse_id}");
        Log::info("Current Nurse ID: {$nurse->nurse_id}");

        // Verify assignment
        if ($appointment->assigned_nurse_id !== $nurse->nurse_id) {
            Log::warning("Assignment mismatch!");
            return redirect()->back()->with('error', 'This patient is not assigned to you.');
        }

        $appointment->update([
            'called_at' => now(),
            'status' => Appointment::STATUS_VITALS_PENDING
        ]);

        Log::info("Patient status updated to VITALS_PENDING");

        // Update assignment to in_progress
        $assignment = PatientNurseAssignment::where('appointment_id', $appointmentId)
            ->where('nurse_id', $nurse->nurse_id)
            ->first();

        if ($assignment) {
            $assignment->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
            Log::info("Assignment status updated to in_progress");
        } else {
            Log::warning("No PatientNurseAssignment record found!");
        }

        // TODO: Broadcast to TV display
        // event(new PatientCalled($appointment));
        
        return redirect()
            ->route('nurse.patients', ['filter' => 'under_checkup', 'highlight' => $appointment->patient_id])
            ->with('success', "{$appointment->patient->user->name} has been called. Please record vitals now.")
            ->with('open_vitals_modal', $appointment->patient_id);
    }
}