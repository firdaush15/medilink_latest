<?php
//CURRENT APPOINTMENT MODEL
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Appointment extends Model
{
    protected $primaryKey = 'appointment_id';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'status',
        'arrived_at',
        'checked_in_by',
        'vitals_completed_at',
        'vitals_recorded_by',
        'vitals_verified_at',
        'consultation_started_at',
        'consultation_ended_at',
        'reason',
        'cancelled_reason',
        'queue_number',
        'queue_priority_score',
        'is_late',
        'late_penalty_minutes',
        'estimated_call_time',
        'called_at',
        'critical_vitals_alert_sent',

        // ✅ ADD THESE FOUR LINES IF MISSING:
        'assigned_nurse_id',
        'nurse_assigned_at',
        'nurse_accepted_at',
        'nurse_notified',

        'is_walk_in',
        'urgency_level',
        'walk_in_notes',
        'checked_out_at',
        'checked_out_by',
        'payment_collected',
        'payment_amount',
        'checkout_notes',

        // Add to your existing $fillable array:
        'consultation_fee',
        'procedures_fee',
        'pharmacy_fee',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'billing_notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime',
        'arrived_at' => 'datetime',
        'vitals_completed_at' => 'datetime',
        'vitals_verified_at' => 'datetime',
        'consultation_started_at' => 'datetime',
        'consultation_ended_at' => 'datetime',
        'estimated_call_time' => 'datetime',
        'called_at' => 'datetime',
        'is_late' => 'boolean',
        'critical_vitals_alert_sent' => 'boolean',
        'nurse_assigned_at' => 'datetime',      // ✅ ADDED
        'nurse_accepted_at' => 'datetime',      // ✅ ADDED
        'nurse_notified' => 'boolean',          // ✅ ADDED
        'checked_out_at' => 'datetime',
        'payment_collected' => 'boolean',
        'is_walk_in' => 'boolean',

        // Add to your existing $casts array:
        'consultation_fee' => 'decimal:2',
        'procedures_fee' => 'decimal:2',
        'pharmacy_fee' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected $appends = ['formatted_queue_number'];

    // ========================================
    // STATUS CONSTANTS
    // ========================================
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CHECKED_IN = 'checked_in';
    const STATUS_VITALS_PENDING = 'vitals_pending';
    const STATUS_VITALS_RECORDED = 'vitals_recorded';
    const STATUS_READY_FOR_DOCTOR = 'ready_for_doctor';
    const STATUS_IN_CONSULTATION = 'in_consultation';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';

    // ========================================
    // RELATIONSHIPS
    // ========================================
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function receptionistWhoCheckedIn()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function nurseWhoRecordedVitals()
    {
        return $this->belongsTo(Nurse::class, 'vitals_recorded_by', 'nurse_id');
    }

    // ✅ FIXED: Added nurse assignment relationships
    public function assignedNurse()
    {
        return $this->belongsTo(Nurse::class, 'assigned_nurse_id', 'nurse_id');
    }

    public function nurseAssignment()
    {
        return $this->hasOne(PatientNurseAssignment::class, 'appointment_id')
            ->latest('assigned_at');
    }

    public function vitals()
    {
        return $this->hasMany(VitalRecord::class, 'appointment_id');
    }

    public function latestVital()
    {
        return $this->hasOne(VitalRecord::class, 'appointment_id')
            ->latest('recorded_at');
    }

    public function workflowLogs()
    {
        return $this->hasMany(AppointmentWorkflowLog::class, 'appointment_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'appointment_id', 'appointment_id');
    }

    // ========================================
    // BILLING RELATIONSHIPS (ADD TO EXISTING RELATIONSHIPS SECTION)
    // ========================================

    public function billingItems()
    {
        return $this->hasMany(BillingItem::class, 'appointment_id', 'appointment_id');
    }

    public function checkedOutBy()
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    // ========================================
    // STATUS CHECKS
    // ========================================
    public function isScheduled()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function hasArrived()
    {
        return !is_null($this->arrived_at);
    }

    public function isCheckedIn()
    {
        return $this->status === self::STATUS_CHECKED_IN;
    }

    public function needsVitals()
    {
        return $this->status === self::STATUS_VITALS_PENDING;
    }

    public function hasVitalsRecorded()
    {
        return in_array($this->status, [
            self::STATUS_VITALS_RECORDED,
            self::STATUS_READY_FOR_DOCTOR,
            self::STATUS_IN_CONSULTATION,
        ]);
    }

    public function isReadyForDoctor()
    {
        return $this->status === self::STATUS_READY_FOR_DOCTOR;
    }

    public function isWithDoctor()
    {
        return $this->status === self::STATUS_IN_CONSULTATION;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Diagnoses recorded during this appointment
     */
    public function diagnoses()
    {
        return $this->hasMany(PatientDiagnosis::class, 'appointment_id', 'appointment_id');
    }

    /**
     * Primary diagnosis for this appointment
     */
    public function primaryDiagnosis()
    {
        return $this->hasOne(PatientDiagnosis::class, 'appointment_id', 'appointment_id')
            ->where('diagnosis_type', 'Primary')
            ->latest('diagnosis_date');
    }

    // ========================================
    // WORKFLOW ACTIONS
    // ========================================

    /**
     * Step 1: Receptionist checks in patient
     */
    public function checkInPatient($receptionistId)
    {
        if (!$this->canCheckIn()) {
            throw new \Exception('Cannot check in: Invalid current state');
        }

        if (!$this->appointment_date->isToday()) {
            throw new \Exception('Can only check in patients for today\'s appointments');
        }

        $this->update([
            'status' => self::STATUS_CHECKED_IN,
            'arrived_at' => now(),
            'checked_in_by' => $receptionistId,
        ]);

        $this->logWorkflowChange(
            $this->getOriginal('status'),
            self::STATUS_CHECKED_IN,
            $receptionistId,
            'user',
            'Patient checked in by receptionist'
        );

        return $this;
    }

    /**
     * Step 2: Nurse starts recording vitals
     */
    public function startVitalsRecording($nurseId)
    {
        if (!$this->canStartVitals()) {
            throw new \Exception('Cannot start vitals: Patient has not been checked in');
        }

        $this->update([
            'status' => self::STATUS_VITALS_PENDING,
        ]);

        $this->logWorkflowChange(
            self::STATUS_CHECKED_IN,
            self::STATUS_VITALS_PENDING,
            $nurseId,
            'nurse',
            'Nurse started recording vitals'
        );

        return $this;
    }

    /**
     * Step 3: Nurse completes vitals recording
     */
    public function completeVitalsRecording($nurseId)
    {
        $latestVital = $this->latestVital;

        if (!$latestVital || !$latestVital->recorded_at->isToday()) {
            throw new \Exception('No vitals recorded today. Please record vitals first.');
        }

        $isCritical = $latestVital->is_critical;

        $this->update([
            'status' => self::STATUS_VITALS_RECORDED,
            'vitals_completed_at' => now(),
            'vitals_recorded_by' => $nurseId,
        ]);

        $this->logWorkflowChange(
            self::STATUS_VITALS_PENDING,
            self::STATUS_VITALS_RECORDED,
            $nurseId,
            'nurse',
            'Vitals recorded' . ($isCritical ? ' - CRITICAL ALERT' : '')
        );

        if ($isCritical && !$this->critical_vitals_alert_sent) {
            $this->sendCriticalVitalsAlert();
        }

        return $this;
    }

    /**
     * Step 4: Nurse marks ready for doctor
     */
    public function markReadyForDoctor($nurseId = null)
    {
        if (!$this->canMarkReadyForDoctor()) {
            throw new \Exception('Cannot mark ready: Vitals must be recorded first');
        }

        $this->update([
            'status' => self::STATUS_READY_FOR_DOCTOR,
            'vitals_verified_at' => now(),
        ]);

        $this->logWorkflowChange(
            self::STATUS_VITALS_RECORDED,
            self::STATUS_READY_FOR_DOCTOR,
            $nurseId,
            'nurse',
            'Patient verified and ready for doctor consultation'
        );

        return $this;
    }

    /**
     * Step 5: Doctor starts consultation
     */
    public function startConsultation($doctorId)
    {
        if (!$this->isReadyForDoctor()) {
            throw new \Exception('Patient is not ready for consultation');
        }

        $this->update([
            'status' => self::STATUS_IN_CONSULTATION,
            'consultation_started_at' => now(),
            'called_at' => now(),
        ]);

        $this->logWorkflowChange(
            self::STATUS_READY_FOR_DOCTOR,
            self::STATUS_IN_CONSULTATION,
            $doctorId,
            'doctor',
            'Consultation started'
        );

        return $this;
    }

    /**
     * Step 6: Doctor completes consultation
     */
    public function completeConsultation($doctorId)
    {
        if (!$this->isWithDoctor()) {
            throw new \Exception('Consultation has not started');
        }

        $duration = $this->consultation_started_at->diffInMinutes(now());

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'consultation_ended_at' => now(),
        ]);

        $this->logWorkflowChange(
            self::STATUS_IN_CONSULTATION,
            self::STATUS_COMPLETED,
            $doctorId,
            'doctor',
            "Consultation completed (Duration: {$duration} minutes)"
        );

        return $this;
    }

    // ========================================
    // VALIDATION HELPERS
    // ========================================
    public function canCheckIn()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function canStartVitals()
    {
        return $this->status === self::STATUS_CHECKED_IN;
    }

    public function canMarkReadyForDoctor()
    {
        if ($this->status !== self::STATUS_VITALS_RECORDED) {
            return false;
        }

        return $this->vitals()
            ->whereDate('recorded_at', today())
            ->exists();
    }

    // ========================================
    // CRITICAL VITALS ALERT
    // ========================================
    protected function sendCriticalVitalsAlert()
    {
        \App\Models\StaffAlert::create([
            'sender_id' => auth()->id(),
            'sender_type' => 'system',
            'recipient_id' => $this->doctor->user_id,
            'recipient_type' => 'doctor',
            'patient_id' => $this->patient_id,
            'alert_type' => 'Critical Vitals',
            'alert_title' => '⚠️ CRITICAL: Abnormal Vital Signs Detected',
            'alert_message' => "Patient {$this->patient->user->name} has critical vital signs. Immediate attention required.",
            'priority' => 'Critical',
            'action_url' => route('doctor.patients.show', $this->patient_id),
        ]);

        $this->update(['critical_vitals_alert_sent' => true]);
    }

    // ========================================
    // WORKFLOW LOGGING
    // ========================================
    protected function logWorkflowChange($fromStatus, $toStatus, $userId, $userType, $notes = null)
    {
        \App\Models\AppointmentWorkflowLog::create([
            'appointment_id' => $this->appointment_id,
            'from_stage' => $fromStatus,
            'to_stage' => $toStatus,
            'changed_by_id' => $userId,
            'changed_by_type' => $userType,
            'notes' => $notes,
            'timestamp' => now(),
            'ip_address' => request()->ip(),
        ]);
    }

    // ========================================
    // DISPLAY HELPERS
    // ========================================
    public function getCurrentStage()
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_CHECKED_IN => 'Checked In',
            self::STATUS_VITALS_PENDING => 'Vitals Recording',
            self::STATUS_VITALS_RECORDED => 'Vitals Recorded',
            self::STATUS_READY_FOR_DOCTOR => 'Ready for Doctor',
            self::STATUS_IN_CONSULTATION => 'With Doctor',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_NO_SHOW => 'No Show',
            default => 'Unknown',
        };
    }

    public function getCurrentStageDisplay()
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED => '📅 Confirmed',
            self::STATUS_CHECKED_IN => '✅ Checked In',
            self::STATUS_VITALS_PENDING => '📋 Vitals Recording',
            self::STATUS_VITALS_RECORDED => '✅ Vitals Recorded',
            self::STATUS_READY_FOR_DOCTOR => '👨‍⚕️ Ready for Doctor',
            self::STATUS_IN_CONSULTATION => '🩺 In Consultation',
            self::STATUS_COMPLETED => '✔️ Completed',
            self::STATUS_CANCELLED => '❌ Cancelled',
            self::STATUS_NO_SHOW => '⚠️ No Show',
            default => 'Unknown',
        };
    }

    public function getStageClass()
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED => 'badge-secondary',
            self::STATUS_CHECKED_IN => 'badge-info',
            self::STATUS_VITALS_PENDING => 'badge-warning',
            self::STATUS_VITALS_RECORDED => 'badge-primary',
            self::STATUS_READY_FOR_DOCTOR => 'badge-success',
            self::STATUS_IN_CONSULTATION => 'badge-purple',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-danger',
            self::STATUS_NO_SHOW => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function getWorkflowProgress()
    {
        $stages = [
            self::STATUS_CONFIRMED,
            self::STATUS_CHECKED_IN,
            self::STATUS_VITALS_RECORDED,
            self::STATUS_READY_FOR_DOCTOR,
            self::STATUS_IN_CONSULTATION,
            self::STATUS_COMPLETED,
        ];

        $currentIndex = array_search($this->status, $stages);
        $totalStages = count($stages);

        return [
            'current_stage' => $this->status,
            'current_stage_display' => $this->getCurrentStageDisplay(),
            'progress_percentage' => $currentIndex !== false ? ($currentIndex / ($totalStages - 1)) * 100 : 0,
            'completed_stages' => $currentIndex !== false ? $currentIndex : 0,
            'total_stages' => $totalStages,
        ];
    }

    // ========================================
    // QUEUE HELPERS
    // ========================================

    public function getFormattedQueueNumberAttribute()
    {
        if (!$this->queue_priority_score) {
            return null;
        }

        $position = $this->getQueuePosition();
        return $position ? "Position {$position}" : null;
    }

    public function isNextInQueue()
    {
        if (!$this->queue_priority_score) {
            return false;
        }

        if (!$this->hasArrived() || $this->isWithDoctor()) {
            return false;
        }

        $betterPriorityExists = self::whereDate('appointment_date', $this->appointment_date)
            ->where('doctor_id', $this->doctor_id)
            ->whereIn('status', [
                self::STATUS_CHECKED_IN,
                self::STATUS_VITALS_PENDING,
                self::STATUS_VITALS_RECORDED,
                self::STATUS_READY_FOR_DOCTOR
            ])
            ->where('queue_priority_score', '<', $this->queue_priority_score)
            ->exists();

        return !$betterPriorityExists;
    }

    public function getPatientsAheadCount()
    {
        if (!$this->queue_priority_score) {
            return 0;
        }

        return self::whereDate('appointment_date', $this->appointment_date)
            ->where('doctor_id', $this->doctor_id)
            ->whereIn('status', [
                self::STATUS_CHECKED_IN,
                self::STATUS_VITALS_PENDING,
                self::STATUS_VITALS_RECORDED,
                self::STATUS_READY_FOR_DOCTOR
            ])
            ->where('queue_priority_score', '<', $this->queue_priority_score)
            ->whereNull('consultation_started_at')
            ->count();
    }

    public function getQueuePosition()
    {
        if (!$this->queue_priority_score) {
            return null;
        }

        return $this->getPatientsAheadCount() + 1;
    }

    public function getTotalPatientsInQueue()
    {
        return self::whereDate('appointment_date', $this->appointment_date)
            ->where('doctor_id', $this->doctor_id)
            ->whereIn('status', [
                self::STATUS_CHECKED_IN,
                self::STATUS_VITALS_PENDING,
                self::STATUS_VITALS_RECORDED,
                self::STATUS_READY_FOR_DOCTOR
            ])
            ->whereNull('consultation_started_at')
            ->count();
    }

    // ========================================
    // QUERY HELPERS
    // ========================================
    public function hasVitalRecordsToday()
    {
        return $this->vitals()
            ->whereDate('recorded_at', today())
            ->exists();
    }

    public function getTodayVitals()
    {
        return $this->vitals()
            ->whereDate('recorded_at', today())
            ->latest('recorded_at')
            ->first();
    }

// ========================================
// BILLING HELPER METHODS (ADD TO END OF CLASS)
// ========================================

    /**
     * Get total billing amount (all items)
     */
    public function getTotalBillingAmount()
    {
        return $this->billingItems->sum('amount');
    }

    /**
     * Get procedures/tests billing (excluding medications)
     */
    public function getProceduresBillingAmount()
    {
        return $this->billingItems()
            ->excludingMedications()
            ->sum('amount');
    }

    /**
     * Get medications billing amount
     */
    public function getMedicationsBillingAmount()
    {
        return $this->billingItems()
            ->where('item_type', 'medication')
            ->sum('amount');
    }

    /**
     * Check if appointment has been checked out
     */
    public function isCheckedOut()
    {
        return !is_null($this->checked_out_at);
    }

    /**
     * Check if payment has been collected
     */
    public function isPaid()
    {
        return $this->payment_collected;
    }

    /**
     * Get billing summary for display
     */
    public function getBillingSummary()
    {
        return [
            'consultation_fee' => $this->consultation_fee,
            'procedures_fee' => $this->procedures_fee,
            'pharmacy_fee' => $this->pharmacy_fee,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'payment_method' => $this->payment_method,
            'payment_collected' => $this->payment_collected,
            'checked_out_at' => $this->checked_out_at,
        ];
    }
}
