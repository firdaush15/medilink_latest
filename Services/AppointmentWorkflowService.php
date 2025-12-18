<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentHandoff;
use App\Models\NurseAlert;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AppointmentWorkflowService
{
    protected $queueService;

    public function __construct(QueueManagementService $queueService)
    {
        $this->queueService = $queueService;
    }

    // ========================================
    // STEP 1: RECEPTIONIST CHECK-IN
    // ========================================

    /**
     * Complete check-in process with handoff to nurse
     * 
     * @return array ['appointment' => Appointment, 'queue_number' => int]
     */
    public function checkInPatient($appointmentId, $receptionistId)
    {
        return DB::transaction(function () use ($appointmentId, $receptionistId) {
            $appointment = Appointment::with(['patient.user', 'doctor.user'])
                ->findOrFail($appointmentId);

            // Validation
            if (!$appointment->canMarkArrived()) {
                throw new \Exception('Cannot check in: Invalid appointment state');
            }

            // Step 1: Mark as arrived
            $appointment->markArrived($receptionistId);

            // Step 2: Assign queue number
            $queueNumber = $this->queueService->assignQueueNumber($appointmentId);

            // Step 3: Create handoff to nurse
            $this->createHandoff(
                $appointment,
                'receptionist_to_nurse',
                $receptionistId,
                $this->getAvailableNurse($appointment->doctor_id),
                "Patient checked in. Queue: Q" . str_pad($queueNumber, 3, '0', STR_PAD_LEFT)
            );

            // Step 4: Notify assigned nurse
            $this->notifyNurse(
                $appointment,
                'New Patient Arrival',
                "Patient {$appointment->patient->user->name} has checked in and is waiting for vitals recording."
            );

            Log::info("Patient checked in successfully", [
                'appointment_id' => $appointmentId,
                'patient' => $appointment->patient->user->name,
                'queue_number' => $queueNumber,
                'receptionist' => $receptionistId,
            ]);

            return [
                'appointment' => $appointment->fresh(),
                'queue_number' => $queueNumber,
            ];
        });
    }

    // ========================================
    // STEP 2: NURSE RECORDS VITALS
    // ========================================

    /**
     * Record vitals with validation and handoff
     * 
     * @param int $appointmentId
     * @param int $nurseId
     * @param array $vitalData
     * @return Appointment
     */
    public function recordVitalsAndProgress($appointmentId, $nurseId, $vitalData)
    {
        return DB::transaction(function () use ($appointmentId, $nurseId, $vitalData) {
            $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($appointmentId);

            // Step 1: Start vitals recording (if not started)
            if ($appointment->workflow_stage === Appointment::STAGE_ARRIVED) {
                $appointment->startVitalsRecording($nurseId);
            }

            // Step 2: Create vital record (handled by controller)
            // Assume vital record is already created via NursePatientsController::storeVitals

            // Step 3: Complete vitals recording
            $appointment->fresh()->completeVitalsRecording($nurseId);

            // Step 4: Auto-verify and mark ready (if vitals are not critical)
            $latestVital = $appointment->fresh()->latestVital;
            
            if ($latestVital && !$latestVital->is_critical) {
                // ✅ Non-critical: Auto-mark ready for doctor
                $appointment->fresh()->markReadyForDoctor($nurseId);
                
                // Create handoff to doctor
                $this->createHandoff(
                    $appointment,
                    'nurse_to_doctor',
                    $nurseId,
                    $appointment->doctor->user_id,
                    "Vitals recorded. Patient ready for consultation."
                );

                // Notify doctor
                $this->notifyDoctor(
                    $appointment,
                    'Patient Ready for Consultation',
                    "Patient {$appointment->patient->user->name} vitals recorded. Ready for consultation."
                );

            } else if ($latestVital && $latestVital->is_critical) {
                // ✅ Critical: Require nurse verification before marking ready
                $this->createCriticalVitalsHandoff(
                    $appointment,
                    $nurseId,
                    "CRITICAL VITALS DETECTED: Immediate attention required"
                );

                // Send urgent notification
                $this->notifyDoctor(
                    $appointment,
                    '🚨 URGENT: Critical Vital Signs',
                    "Patient {$appointment->patient->user->name} has abnormal vital signs. Review immediately.",
                    'urgent'
                );
            }

            Log::info("Vitals recorded and workflow progressed", [
                'appointment_id' => $appointmentId,
                'nurse_id' => $nurseId,
                'is_critical' => $latestVital?->is_critical ?? false,
            ]);

            return $appointment->fresh();
        });
    }

    /**
     * ✅ NEW: Nurse manually verifies and marks ready (for critical cases)
     */
    public function verifyAndMarkReady($appointmentId, $nurseId, $verificationNotes = null)
    {
        return DB::transaction(function () use ($appointmentId, $nurseId, $verificationNotes) {
            $appointment = Appointment::findOrFail($appointmentId);

            if (!$appointment->canMarkReadyForDoctor()) {
                throw new \Exception('Cannot mark ready: Vitals not recorded or already marked');
            }

            // Mark ready with verification
            $appointment->markReadyForDoctor($nurseId);

            // Create handoff to doctor
            $this->createHandoff(
                $appointment,
                'nurse_to_doctor',
                $nurseId,
                $appointment->doctor->user_id,
                $verificationNotes ?? "Patient verified and ready for consultation"
            );

            // Notify doctor
            $this->notifyDoctor(
                $appointment,
                'Patient Ready for Consultation',
                "Patient {$appointment->patient->user->name} has been verified and is ready."
            );

            Log::info("Patient manually verified and marked ready", [
                'appointment_id' => $appointmentId,
                'nurse_id' => $nurseId,
            ]);

            return $appointment->fresh();
        });
    }

    // ========================================
    // STEP 3: DOCTOR CONSULTATION
    // ========================================

    /**
     * Start doctor consultation
     */
    public function startConsultation($appointmentId, $doctorId)
    {
        return DB::transaction(function () use ($appointmentId, $doctorId) {
            $appointment = Appointment::findOrFail($appointmentId);

            if (!$appointment->isReadyForDoctor()) {
                throw new \Exception('Patient is not ready for consultation');
            }

            // Start consultation
            $appointment->startConsultation($doctorId);

            // Mark as called in queue
            $this->queueService->markPatientCalled($appointmentId);

            // Acknowledge handoff from nurse
            $this->acknowledgeLatestHandoff($appointment, $doctorId);

            Log::info("Consultation started", [
                'appointment_id' => $appointmentId,
                'doctor_id' => $doctorId,
            ]);

            return $appointment->fresh();
        });
    }

    /**
     * Complete doctor consultation
     */
    public function completeConsultation($appointmentId, $doctorId)
    {
        return DB::transaction(function () use ($appointmentId, $doctorId) {
            $appointment = Appointment::findOrFail($appointmentId);

            if (!$appointment->isWithDoctor()) {
                throw new \Exception('Consultation has not started');
            }

            // Complete consultation
            $appointment->completeConsultation($doctorId);

            Log::info("Consultation completed", [
                'appointment_id' => $appointmentId,
                'doctor_id' => $doctorId,
                'duration_minutes' => $appointment->consultation_started_at->diffInMinutes($appointment->consultation_ended_at),
            ]);

            return $appointment->fresh();
        });
    }

    // ========================================
    // HANDOFF MANAGEMENT
    // ========================================

    /**
     * Create handoff between staff members
     */
    protected function createHandoff($appointment, $handoffType, $fromUserId, $toUserId, $notes = null, $requiresImmediateAttention = false)
    {
        return AppointmentHandoff::create([
            'appointment_id' => $appointment->appointment_id,
            'handoff_type' => $handoffType,
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'initiated_at' => now(),
            'handoff_notes' => $notes,
            'requires_immediate_attention' => $requiresImmediateAttention,
        ]);
    }

    /**
     * Create critical vitals handoff
     */
    protected function createCriticalVitalsHandoff($appointment, $nurseId, $criticalInfo)
    {
        return $this->createHandoff(
            $appointment,
            'nurse_to_doctor',
            $nurseId,
            $appointment->doctor->user_id,
            $criticalInfo,
            true // Requires immediate attention
        );
    }

    /**
     * Acknowledge handoff
     */
    protected function acknowledgeLatestHandoff($appointment, $userId)
    {
        $handoff = AppointmentHandoff::where('appointment_id', $appointment->appointment_id)
            ->where('to_user_id', $userId)
            ->where('is_acknowledged', false)
            ->latest('initiated_at')
            ->first();

        if ($handoff) {
            $handoff->update([
                'is_acknowledged' => true,
                'acknowledged_at' => now(),
            ]);
        }
    }

    // ========================================
    // NOTIFICATION SYSTEM
    // ========================================

    /**
     * Notify nurse about new patient
     */
    protected function notifyNurse($appointment, $title, $message, $priority = 'normal')
    {
        $nurse = $this->getAvailableNurse($appointment->doctor_id);

        if ($nurse) {
            NurseAlert::create([
                'nurse_id' => $nurse->nurse_id,
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->appointment_id,
                'alert_type' => 'Appointment Reminder',
                'alert_title' => $title,
                'alert_message' => $message,
                'priority' => ucfirst($priority),
                'action_url' => route('nurse.patients.show', $appointment->patient_id),
            ]);
        }
    }

    /**
     * Notify doctor
     */
    protected function notifyDoctor($appointment, $title, $message, $priority = 'normal')
    {
        SystemNotification::create([
            'user_id' => $appointment->doctor->user_id,
            'user_role' => 'doctor',
            'notification_type' => 'patient_ready',
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'is_actionable' => true,
            'action_url' => route('doctor.appointments.show', $appointment->appointment_id),
            'data' => json_encode([
                'appointment_id' => $appointment->appointment_id,
                'patient_name' => $appointment->patient->user->name,
                'queue_number' => $appointment->queue_number,
            ]),
        ]);
    }

    /**
     * Get available nurse for doctor's department
     * 
     * @return \App\Models\Nurse|null
     */
    protected function getAvailableNurse($doctorId)
    {
        $doctor = \App\Models\Doctor::find($doctorId);
        
        // Get nurse from same department/specialization
        return \App\Models\Nurse::where('availability_status', 'Available')
            ->where('department', $doctor?->specialization)
            ->first() 
            ?? \App\Models\Nurse::where('availability_status', 'Available')->first();
    }

    // ========================================
    // WORKFLOW QUERIES
    // ========================================

    /**
     * Get pending handoffs for user
     */
    public function getPendingHandoffs($userId)
    {
        return AppointmentHandoff::with(['appointment.patient.user', 'fromUser'])
            ->where('to_user_id', $userId)
            ->where('is_acknowledged', false)
            ->orderByDesc('requires_immediate_attention')
            ->orderBy('initiated_at', 'asc')
            ->get();
    }

    /**
     * Get workflow timeline for appointment
     */
    public function getWorkflowTimeline($appointmentId)
    {
        $appointment = Appointment::with(['workflowLogs.changedBy'])->findOrFail($appointmentId);

        return $appointment->workflowLogs->map(function ($log) {
            return [
                'stage' => $log->to_stage,
                'timestamp' => $log->timestamp,
                'changed_by' => $log->changedBy->name,
                'notes' => $log->notes,
            ];
        });
    }
}