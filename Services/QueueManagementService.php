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
            'late_penalty_per_minute' => 1000, // Heavy penalty to push late patients to back
            'appointment_time_weight' => 100,
            'arrival_time_weight' => 10,
            'allow_walk_ins' => false,
            'walk_in_penalty_minutes' => 120,
            'avg_consultation_minutes' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * ✅ SIMPLIFIED: Just calculate priority score, no queue numbers
     */
    public function assignPriority($appointmentId, $arrivalTime = null)
    {
        Log::info("=== Priority Assignment Started ===");
        Log::info("Appointment ID: $appointmentId");

        try {
            $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($appointmentId);
            
            $arrivalTime = $appointment->arrived_at ?? $arrivalTime ?? now();
            
            Log::info("Patient: " . $appointment->patient->user->name);
            Log::info("Appointment Time: " . $appointment->appointment_time);
            Log::info("Arrival Time: " . $arrivalTime);

            // Calculate if patient is late
            $appointmentTime = Carbon::parse(
                $appointment->appointment_date->format('Y-m-d') . ' ' . 
                $appointment->appointment_time->format('H:i:s')
            );
            
            $gracePeriodEnd = $appointmentTime->copy()->addMinutes($this->settings->grace_period_minutes);
            $isLate = $arrivalTime->gt($gracePeriodEnd);
            $lateMinutes = $isLate ? $arrivalTime->diffInMinutes($gracePeriodEnd) : 0;

            Log::info("Is Late: " . ($isLate ? 'YES' : 'NO'));
            Log::info("Late Minutes: " . $lateMinutes);

            // ✅ PRIORITY CALCULATION (Lower score = Higher priority)
            // Base score: Appointment time in minutes from midnight
            $appointmentMinutes = ($appointmentTime->hour * 60) + $appointmentTime->minute;
            $baseScore = $appointmentMinutes * $this->settings->appointment_time_weight;
            
            // Late penalty: HUGE penalty to push late patients to the back
            $latePenalty = $isLate ? ($lateMinutes * $this->settings->late_penalty_per_minute) : 0;
            
            // Early bonus: Small reward for arriving early
            $earlyBonus = 0;
            if ($arrivalTime->lt($appointmentTime)) {
                $earlyMinutes = $appointmentTime->diffInMinutes($arrivalTime);
                $earlyBonus = min($earlyMinutes, 5) * $this->settings->arrival_time_weight;
            }

            // Tiebreaker: Arrival time (earlier arrival = slight advantage)
            $arrivalMinutes = ($arrivalTime->hour * 60) + $arrivalTime->minute;
            $arrivalTiebreaker = $arrivalMinutes * $this->settings->arrival_time_weight;
            
            $priorityScore = $baseScore + $latePenalty - $earlyBonus + $arrivalTiebreaker;

            Log::info("Final Priority Score: $priorityScore");

            // ✅ UPDATE: Only priority score and late flags
            $appointment->update([
                'queue_priority_score' => $priorityScore,
                'is_late' => $isLate,
                'late_penalty_minutes' => $lateMinutes,
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
        $estimatedCallTime = now()->addMinutes($estimatedMinutes);

        $appointment->update([
            'estimated_call_time' => $estimatedCallTime
        ]);
        
        Log::info("Estimated call time: " . $estimatedCallTime->format('H:i') . " ($patientsAhead patients ahead)");
    }

    /**
     * ✅ GET NEXT PATIENT (for nurse to call)
     */
    public function getNextPatient($doctorId = null, $status = ['checked_in', 'vitals_pending'])
    {
        $query = Appointment::with(['patient.user', 'doctor'])
            ->whereDate('appointment_date', today())
            ->whereIn('status', $status)
            ->whereNull('vitals_completed_at')
            ->orderBy('queue_priority_score', 'asc'); // LOWEST score = highest priority

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
     * ✅ GET QUEUE STATUS FOR DOCTOR
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
                    'is_late' => $appointment->is_late,
                    'late_minutes' => $appointment->late_penalty_minutes,
                    'estimated_call_time' => $appointment->estimated_call_time?->format('h:i A'),
                    'current_status' => $appointment->getCurrentStageDisplay(),
                    'wait_time' => $appointment->arrived_at?->diffForHumans(),
                ];
            });
    }

    /**
     * ✅ GET PATIENT QUEUE INFO
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
            'is_late' => $appointment->is_late,
            'late_penalty_applied' => $appointment->late_penalty_minutes > 0,
            'doctor_name' => $appointment->doctor->user->name,
        ];
    }

    public function updateSettings($settingsArray)
    {
        DB::table('queue_settings')->update($settingsArray);
        $this->settings = DB::table('queue_settings')->first();
    }
}