<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueManagementService
{
    protected $settings;

    public function __construct()
    {
        $this->settings = DB::table('queue_settings')->first();
        
        if (!$this->settings) {
            Log::warning('Queue settings not found, creating defaults...');
            $this->createDefaultSettings();
            $this->settings = DB::table('queue_settings')->first();
        }
    }

    protected function createDefaultSettings()
    {
        DB::table('queue_settings')->insert([
            'grace_period_minutes' => 15,
            'late_penalty_per_minute' => 1000,
            'appointment_time_weight' => 100,
            'arrival_time_weight' => 10,
            'allow_walk_ins' => true, // Enable walk-ins
            'walk_in_penalty_minutes' => 120,
            'avg_consultation_minutes' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * ✅ UPDATED: Priority calculation with urgency support
     * 
     * Priority Score Calculation (Lower = Higher Priority):
     * 1. Emergency walk-ins: 0-999 (ALWAYS first)
     * 2. Urgent walk-ins: 1000-4999 (Before scheduled)
     * 3. Scheduled appointments (on time): 5000-14999
     * 4. Routine walk-ins: 15000-19999
     * 5. Late appointments: 20000+ (Pushed to back)
     */
    public function assignPriority($appointmentId, $arrivalTime = null)
    {
        Log::info("=== Priority Assignment Started ===");
        Log::info("Appointment ID: $appointmentId");

        try {
            $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($appointmentId);
            
            $arrivalTime = $appointment->arrived_at ?? $arrivalTime ?? now();
            
            Log::info("Patient: " . $appointment->patient->user->name);
            Log::info("Is Walk-in: " . ($appointment->is_walk_in ? 'YES' : 'NO'));
            Log::info("Urgency Level: " . ($appointment->urgency_level ?? 'N/A'));

            $priorityScore = $appointment->is_walk_in 
                ? $this->calculateWalkInPriority($appointment, $arrivalTime)
                : $this->calculateScheduledPriority($appointment, $arrivalTime);

            Log::info("Final Priority Score: $priorityScore");

            // Update appointment
            $appointment->update([
                'queue_priority_score' => $priorityScore,
                'is_late' => $appointment->is_walk_in ? false : $this->isLate($appointment, $arrivalTime),
                'late_penalty_minutes' => $appointment->is_walk_in ? 0 : $this->calculateLateMinutes($appointment, $arrivalTime),
            ]);

            Log::info("✅ Priority assigned successfully");

            // Estimate call time
            $this->estimateCallTime($appointment->appointment_id);

            $appointment->refresh();
            Log::info("=== Priority Assignment Completed ===");

            return $priorityScore;

        } catch (\Exception $e) {
            Log::error("=== Priority Assignment FAILED ===");
            Log::error("Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ NEW: Calculate priority for walk-in patients based on urgency
     */
    protected function calculateWalkInPriority($appointment, $arrivalTime)
    {
        $urgencyLevel = $appointment->urgency_level ?? 'routine';
        
        // Arrival time tiebreaker (in minutes from midnight)
        $arrivalMinutes = ($arrivalTime->hour * 60) + $arrivalTime->minute;
        
        switch ($urgencyLevel) {
            case 'emergency':
                // 🔴 EMERGENCY: Score 0-999 (ALWAYS FIRST)
                // Earlier arrivals get slightly lower scores
                $baseScore = 0;
                $tiebreaker = $arrivalMinutes; // 0-1439 (max one day in minutes)
                return $baseScore + $tiebreaker;
                
            case 'urgent':
                // 🟡 URGENT: Score 1000-4999 (Before scheduled appointments)
                // Scheduled appointments typically start at 5000+
                $baseScore = 1000;
                $tiebreaker = min($arrivalMinutes * 2, 3999); // Max 3999 to stay under 5000
                return $baseScore + $tiebreaker;
                
            case 'routine':
            default:
                // 🟢 ROUTINE: Score 15000-19999 (After scheduled appointments)
                $baseScore = 15000;
                $tiebreaker = min($arrivalMinutes * 3, 4999); // Max 4999
                return $baseScore + $tiebreaker;
        }
    }

    /**
     * ✅ UPDATED: Calculate priority for scheduled appointments
     */
    protected function calculateScheduledPriority($appointment, $arrivalTime)
    {
        $appointmentTime = Carbon::parse(
            $appointment->appointment_date->format('Y-m-d') . ' ' . 
            $appointment->appointment_time->format('H:i:s')
        );
        
        // Check if late
        $gracePeriodEnd = $appointmentTime->copy()->addMinutes($this->settings->grace_period_minutes);
        $isLate = $arrivalTime->gt($gracePeriodEnd);
        $lateMinutes = $isLate ? $arrivalTime->diffInMinutes($gracePeriodEnd) : 0;

        // Base score from appointment time (5000-14999 range)
        $appointmentMinutes = ($appointmentTime->hour * 60) + $appointmentTime->minute;
        $baseScore = 5000 + ($appointmentMinutes * 10); // Scale appointment time to fit in range
        
        if ($isLate) {
            // 🚨 LATE PENALTY: Push to back (20000+)
            $latePenalty = 20000 + ($lateMinutes * $this->settings->late_penalty_per_minute);
            return $latePenalty;
        }
        
        // Early arrival bonus (small advantage)
        $earlyBonus = 0;
        if ($arrivalTime->lt($appointmentTime)) {
            $earlyMinutes = $appointmentTime->diffInMinutes($arrivalTime);
            $earlyBonus = min($earlyMinutes, 30) * 5; // Max 150 bonus
        }
        
        // Arrival time tiebreaker
        $arrivalMinutes = ($arrivalTime->hour * 60) + $arrivalTime->minute;
        $arrivalTiebreaker = ($arrivalMinutes % 100); // Small tiebreaker
        
        return max(5000, $baseScore - $earlyBonus + $arrivalTiebreaker);
    }

    /**
     * Helper: Check if patient is late
     */
    protected function isLate($appointment, $arrivalTime)
    {
        $appointmentTime = Carbon::parse(
            $appointment->appointment_date->format('Y-m-d') . ' ' . 
            $appointment->appointment_time->format('H:i:s')
        );
        
        $gracePeriodEnd = $appointmentTime->copy()->addMinutes($this->settings->grace_period_minutes);
        return $arrivalTime->gt($gracePeriodEnd);
    }

    /**
     * Helper: Calculate late minutes
     */
    protected function calculateLateMinutes($appointment, $arrivalTime)
    {
        if (!$this->isLate($appointment, $arrivalTime)) {
            return 0;
        }
        
        $appointmentTime = Carbon::parse(
            $appointment->appointment_date->format('Y-m-d') . ' ' . 
            $appointment->appointment_time->format('H:i:s')
        );
        
        $gracePeriodEnd = $appointmentTime->copy()->addMinutes($this->settings->grace_period_minutes);
        return $arrivalTime->diffInMinutes($gracePeriodEnd);
    }

    /**
     * ✅ UPDATED: Estimate call time considering urgency
     */
    protected function estimateCallTime($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);

        // Count patients with BETTER priority (lower score)
        $patientsAhead = Appointment::whereDate('appointment_date', $appointment->appointment_date)
            ->where('doctor_id', $appointment->doctor_id)
            ->whereIn('status', [
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
                Appointment::STATUS_VITALS_RECORDED,
                Appointment::STATUS_READY_FOR_DOCTOR
            ])
            ->where('queue_priority_score', '<', $appointment->queue_priority_score)
            ->count();

        $estimatedMinutes = $patientsAhead * $this->settings->avg_consultation_minutes;
        
        // Emergency walk-ins: estimate within 5 minutes
        if ($appointment->is_walk_in && $appointment->urgency_level === 'emergency') {
            $estimatedMinutes = min($estimatedMinutes, 5);
        }
        
        $estimatedCallTime = now()->addMinutes($estimatedMinutes);

        $appointment->update([
            'estimated_call_time' => $estimatedCallTime
        ]);
        
        Log::info("Estimated call time: " . $estimatedCallTime->format('H:i') . " ($patientsAhead patients ahead)");
    }

    /**
     * ✅ GET NEXT PATIENT (for nurse to call)
     * ALWAYS sorted by priority score (lowest = highest priority)
     */
    public function getNextPatient($doctorId = null, $status = ['checked_in', 'vitals_pending'])
    {
        $query = Appointment::with(['patient.user', 'doctor'])
            ->whereDate('appointment_date', today())
            ->whereIn('status', $status)
            ->whereNull('vitals_completed_at')
            ->orderBy('queue_priority_score', 'asc'); // 🔥 This ensures emergencies come first

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        return $query->first();
    }

    /**
     * ✅ GET UPCOMING PATIENTS (for display screen)
     */
    public function getUpcomingPatients($limit = 5, $doctorId = null)
    {
        $query = Appointment::with(['patient.user'])
            ->whereDate('appointment_date', today())
            ->whereIn('status', [
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
                Appointment::STATUS_VITALS_RECORDED,
                Appointment::STATUS_READY_FOR_DOCTOR
            ])
            ->orderBy('queue_priority_score', 'asc')
            ->take($limit);

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        return $query->get();
    }

    /**
     * ✅ GET QUEUE STATUS FOR DOCTOR (with urgency indicators)
     */
    public function getQueueForDoctor($doctorId, $date = null)
    {
        $date = $date ?? today();

        return Appointment::with(['patient.user'])
            ->whereDate('appointment_date', $date)
            ->where('doctor_id', $doctorId)
            ->whereIn('status', [
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
                Appointment::STATUS_VITALS_RECORDED,
                Appointment::STATUS_READY_FOR_DOCTOR
            ])
            ->whereNull('consultation_started_at')
            ->orderBy('queue_priority_score', 'asc')
            ->get()
            ->map(function($appointment, $index) {
                return [
                    'position' => $index + 1,
                    'patient_name' => $appointment->patient->user->name,
                    'appointment_time' => $appointment->appointment_time->format('h:i A'),
                    'arrival_time' => $appointment->arrived_at?->format('h:i A'),
                    'is_walk_in' => $appointment->is_walk_in,
                    'urgency_level' => $appointment->urgency_level ?? 'routine',
                    'urgency_badge' => $this->getUrgencyBadge($appointment),
                    'is_late' => $appointment->is_late,
                    'late_minutes' => $appointment->late_penalty_minutes,
                    'estimated_call_time' => $appointment->estimated_call_time?->format('h:i A'),
                    'current_status' => $appointment->getCurrentStageDisplay(),
                    'wait_time' => $appointment->arrived_at?->diffForHumans(),
                    'priority_score' => $appointment->queue_priority_score,
                ];
            });
    }

    /**
     * ✅ NEW: Get urgency badge for display
     */
    protected function getUrgencyBadge($appointment)
    {
        if (!$appointment->is_walk_in) {
            return null;
        }

        return match($appointment->urgency_level) {
            'emergency' => '🔴 EMERGENCY',
            'urgent' => '🟡 URGENT',
            'routine' => '🟢 ROUTINE',
            default => null,
        };
    }

    /**
     * ✅ GET PATIENT QUEUE INFO (with urgency context)
     */
    public function getPatientQueueInfo($appointmentId)
    {
        $appointment = Appointment::with(['patient.user', 'doctor.user'])->findOrFail($appointmentId);

        // Count patients with better priority
        $patientsAhead = Appointment::whereDate('appointment_date', $appointment->appointment_date)
            ->where('doctor_id', $appointment->doctor_id)
            ->whereIn('status', [
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
                Appointment::STATUS_VITALS_RECORDED,
                Appointment::STATUS_READY_FOR_DOCTOR
            ])
            ->where('queue_priority_score', '<', $appointment->queue_priority_score)
            ->count();

        $totalInQueue = Appointment::whereDate('appointment_date', $appointment->appointment_date)
            ->where('doctor_id', $appointment->doctor_id)
            ->whereIn('status', [
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
                Appointment::STATUS_VITALS_RECORDED,
                Appointment::STATUS_READY_FOR_DOCTOR
            ])
            ->count();

        return [
            'patients_ahead' => $patientsAhead,
            'total_in_queue' => $totalInQueue,
            'estimated_call_time' => $appointment->estimated_call_time?->format('h:i A'),
            'estimated_wait_minutes' => $appointment->estimated_call_time ? 
                now()->diffInMinutes($appointment->estimated_call_time) : null,
            'is_walk_in' => $appointment->is_walk_in,
            'urgency_level' => $appointment->urgency_level ?? null,
            'is_late' => $appointment->is_late,
            'late_penalty_applied' => $appointment->late_penalty_minutes > 0,
            'doctor_name' => $appointment->doctor->user->name,
            'priority_explanation' => $this->getPriorityExplanation($appointment),
        ];
    }

    /**
     * ✅ NEW: Explain priority for patient display
     */
    protected function getPriorityExplanation($appointment)
    {
        if ($appointment->is_walk_in) {
            return match($appointment->urgency_level) {
                'emergency' => '🔴 Emergency - You will be seen immediately',
                'urgent' => '🟡 Urgent - You will be prioritized ahead of scheduled appointments',
                'routine' => '🟢 Routine - You will be seen after scheduled appointments',
                default => 'Walk-in patient',
            };
        }

        if ($appointment->is_late) {
            return '⏰ Late arrival - Moved to back of queue';
        }

        return '📅 Scheduled appointment - Normal queue order';
    }

    public function updateSettings($settingsArray)
    {
        DB::table('queue_settings')->update($settingsArray);
        $this->settings = DB::table('queue_settings')->first();
    }
}