<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Nurse;
use App\Models\StaffShift;
use App\Models\NurseDoctorAssignment;
use App\Models\PatientNurseAssignment;
use App\Models\NurseWorkloadTracking;
use App\Models\NurseAssignmentLog;
use App\Models\StaffAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NurseAssignmentService
{
    /**
     * ✅ UPDATED: Auto-assign nurse when patient checks in (shift-aware)
     */
    public function assignNurseToAppointment(Appointment $appointment): ?PatientNurseAssignment
    {
        DB::beginTransaction();

        try {
            Log::info("=== NURSE ASSIGNMENT START (SHIFT-AWARE) ===");
            Log::info("Appointment ID: {$appointment->appointment_id}");
            Log::info("Doctor ID: {$appointment->doctor_id}");
            Log::info("Patient: {$appointment->patient->user->name}");
            Log::info("Current Time: " . now()->format('Y-m-d H:i:s'));

            // ✅ Get nurses who are BOTH assigned to doctor AND on shift
            $eligibleNurses = $this->getEligibleNursesOnShift($appointment->doctor_id);

            Log::info("Eligible nurses on shift: {$eligibleNurses->count()}");

            if ($eligibleNurses->isEmpty()) {
                Log::warning("❌ No nurses on shift for doctor {$appointment->doctor_id}");

                // Debug why no nurses available
                $this->debugShiftEligibility($appointment->doctor_id);

                // Send alert to admin/manager about staffing issue
                $this->sendStaffingAlert($appointment);

                // Fallback: Send to all nurses (even off-shift) as emergency
                $this->sendAlertToAllAvailableNurses($appointment);

                DB::commit();
                return null;
            }

            // Select best nurse from those on shift
            $selectedNurse = $this->selectBestNurse($eligibleNurses);

            if (!$selectedNurse) {
                Log::warning("❌ No nurse available for assignment (selection failed)");
                $this->sendAlertToAllAvailableNurses($appointment);
                DB::commit();
                return null;
            }

            Log::info("✅ Selected nurse: {$selectedNurse->user->name} (ID: {$selectedNurse->nurse_id})");

            // Create assignment (auto-accepted for outpatient)
            $assignment = PatientNurseAssignment::create([
                'appointment_id' => $appointment->appointment_id,
                'patient_id' => $appointment->patient_id,
                'nurse_id' => $selectedNurse->nurse_id,
                'assignment_method' => 'auto',
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            // Update appointment
            $appointment->update([
                'assigned_nurse_id' => $selectedNurse->nurse_id,
                'nurse_assigned_at' => now(),
                'nurse_accepted_at' => now(),
                'nurse_notified' => false,
            ]);

            // Update workload
            $this->incrementNurseWorkload($selectedNurse->nurse_id);

            // Log assignment
            NurseAssignmentLog::create([
                'appointment_id' => $appointment->appointment_id,
                'nurse_id' => $selectedNurse->nurse_id,
                'action' => 'auto_assigned',
                'details' => "Auto-assigned (on-shift). Workload: {$selectedNurse->workload->current_patients}",
                'workload_at_time' => $selectedNurse->workload->current_patients,
                'action_at' => now(),
                'ip_address' => request()->ip(),
            ]);

            // Send notification
            $this->sendAssignmentNotification($appointment, $selectedNurse);

            DB::commit();

            Log::info("✅ ASSIGNMENT SUCCESSFUL: Nurse {$selectedNurse->user->name} (ON SHIFT) assigned to patient {$appointment->patient->user->name}");

            return $assignment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ ASSIGNMENT FAILED: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            $this->sendAlertToAllAvailableNurses($appointment);
            return null;
        }
    }

    /**
     * ✅ NEW: Get eligible nurses who are BOTH assigned to doctor AND currently on shift
     */
    private function getEligibleNursesOnShift($doctorId)
    {
        $now = Carbon::now();
        $currentDate = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');

        Log::info("=== SHIFT-AWARE ELIGIBILITY CHECK ===");
        Log::info("Current Date: {$currentDate}");
        Log::info("Current Time: {$currentTime}");

        // Step 1: Get nurses assigned to this doctor
        $assignedNurseIds = NurseDoctorAssignment::where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->pluck('nurse_id');

        Log::info("Nurses assigned to doctor: " . $assignedNurseIds->count());

        if ($assignedNurseIds->isEmpty()) {
            Log::error("❌ NO nurse-doctor assignments found!");
            return collect();
        }

        // Step 2: Get nurses who are currently on shift
        $nursesOnShift = StaffShift::with(['user.nurse'])
            ->where('shift_date', $currentDate)
            ->where('staff_role', 'nurse')
            ->where('status', 'checked_in') // Only checked-in shifts
            ->whereTime('start_time', '<=', $currentTime)
            ->whereTime('end_time', '>=', $currentTime)
            ->whereIn('user_id', function ($query) use ($assignedNurseIds) {
                $query->select('user_id')
                    ->from('nurses')
                    ->whereIn('nurse_id', $assignedNurseIds);
            })
            ->get()
            ->pluck('user.nurse.nurse_id')
            ->filter();

        Log::info("Nurses on shift right now: " . $nursesOnShift->count());

        if ($nursesOnShift->isEmpty()) {
            Log::warning("⚠️ No nurses currently on shift!");
            return collect();
        }

        // Step 3: Get eligible nurses (assigned + on shift + available)
        return Nurse::with(['user', 'workload'])
            ->whereIn('nurse_id', $nursesOnShift)
            ->whereIn('availability_status', ['Available', 'On Duty'])
            ->where(function ($query) {
                $query->whereHas('workload', function ($q) {
                    $q->where('is_available', true)
                        ->where('current_status', '!=', 'on_break')
                        ->whereRaw('current_patients < max_capacity');
                });
            })
            ->get()
            ->each(function ($nurse) {
                // Auto-create workload if missing
                if (!$nurse->workload) {
                    NurseWorkloadTracking::create([
                        'nurse_id' => $nurse->nurse_id,
                        'current_patients' => 0,
                        'pending_vitals' => 0,
                        'total_today' => 0,
                        'max_capacity' => 5,
                        'is_available' => true,
                        'current_status' => 'available',
                        'efficiency_score' => 100,
                    ]);
                    $nurse->load('workload');
                    Log::info("✅ Auto-created workload for nurse: {$nurse->user->name}");
                }
            });
    }

    /**
     * ✅ NEW: Debug shift eligibility
     */
    private function debugShiftEligibility($doctorId)
    {
        Log::info("=== DEBUGGING SHIFT ELIGIBILITY ===");

        $now = Carbon::now();
        $currentDate = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');

        // Check doctor-nurse assignments
        $assignments = NurseDoctorAssignment::with('nurse.user')
            ->where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->get();

        Log::info("Doctor-Nurse Assignments: {$assignments->count()}");

        foreach ($assignments as $assignment) {
            $nurse = $assignment->nurse;
            Log::info("  - Nurse: {$nurse->user->name}");

            // Check if nurse has shift today
            $todayShift = StaffShift::where('user_id', $nurse->user_id)
                ->where('shift_date', $currentDate)
                ->where('staff_role', 'nurse')
                ->first();

            if ($todayShift) {
                Log::info("    ✓ Has shift today: {$todayShift->start_time->format('H:i')} - {$todayShift->end_time->format('H:i')}");
                Log::info("    Status: {$todayShift->status}");

                $isInShiftTime = $currentTime >= $todayShift->start_time->format('H:i:s') &&
                    $currentTime <= $todayShift->end_time->format('H:i:s');
                Log::info("    Is in shift time: " . ($isInShiftTime ? 'YES' : 'NO'));
            } else {
                Log::warning("    ❌ NO SHIFT TODAY");
            }

            Log::info("    Availability: {$nurse->availability_status}");

            $workload = $nurse->workload;
            if ($workload) {
                Log::info("    Workload: {$workload->current_patients}/{$workload->max_capacity}");
                Log::info("    Status: {$workload->current_status}");
            } else {
                Log::error("    ❌ NO WORKLOAD RECORD");
            }
        }

        // Check all shifts for today
        Log::info("=== ALL NURSE SHIFTS TODAY ===");
        $allShifts = StaffShift::with('user')
            ->where('shift_date', $currentDate)
            ->where('staff_role', 'nurse')
            ->get();

        Log::info("Total nurse shifts today: {$allShifts->count()}");
        foreach ($allShifts as $shift) {
            Log::info("  - {$shift->user->name}: {$shift->start_time->format('H:i')} - {$shift->end_time->format('H:i')} ({$shift->status})");
        }
    }

    /**
     * ✅ NEW: Send staffing alert to admin when no nurses on shift
     */
    private function sendStaffingAlert(Appointment $appointment)
    {
        Log::warning("⚠️ STAFFING ALERT: No nurses on shift for appointment {$appointment->appointment_id}");

        // Get all admin users
        $admins = \App\Models\User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            StaffAlert::create([
                'sender_id' => auth()->id(),
                'sender_type' => 'system',
                'recipient_id' => $admin->id,
                'recipient_type' => 'admin',
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->appointment_id,
                'alert_type' => 'Staffing Issue',
                'priority' => 'Critical',
                'alert_title' => '🚨 NO NURSES ON SHIFT',
                'alert_message' => "Patient {$appointment->patient->user->name} checked in for Dr. {$appointment->doctor->user->name}, but NO nurses are currently on shift. Immediate attention required!",
                'action_url' => route('admin.shifts.index'),
            ]);
        }
    }

    /**
     * Select best nurse using scoring
     */
    private function selectBestNurse($nurses)
    {
        if ($nurses->isEmpty()) {
            return null;
        }

        $scoredNurses = $nurses->map(function ($nurse) {
            $score = 0;
            $workload = $nurse->workload;

            // Workload (40%)
            $capacityUtilization = $workload->current_patients / $workload->max_capacity;
            $score += (1 - $capacityUtilization) * 40;

            // Pending vitals (25%)
            $score += max(0, (5 - $workload->pending_vitals) / 5) * 25;

            // Efficiency (20%)
            $score += ($workload->efficiency_score / 100) * 20;

            // Time since last assignment (15%)
            if ($workload->last_assignment_at) {
                $minutesSinceLastAssignment = now()->diffInMinutes($workload->last_assignment_at);
                $score += min($minutesSinceLastAssignment / 30, 1) * 15;
            } else {
                $score += 15;
            }

            $nurse->assignment_score = $score;
            Log::info("Nurse {$nurse->user->name} score: {$score}");

            return $nurse;
        });

        return $scoredNurses->sortByDesc('assignment_score')->first();
    }

    /**
     * Increment nurse workload
     */
    private function incrementNurseWorkload($nurseId)
    {
        $workload = NurseWorkloadTracking::firstOrCreate(
            ['nurse_id' => $nurseId],
            [
                'current_patients' => 0,
                'pending_vitals' => 0,
                'total_today' => 0,
                'max_capacity' => 5,
                'is_available' => true,
                'current_status' => 'available',
                'efficiency_score' => 100,
            ]
        );

        $workload->increment('current_patients');
        $workload->increment('pending_vitals');
        $workload->increment('total_today');
        $workload->update([
            'last_assignment_at' => now(),
            'is_available' => $workload->current_patients < $workload->max_capacity,
        ]);
    }

    /**
     * Send notification to assigned nurse
     */
    private function sendAssignmentNotification(Appointment $appointment, Nurse $nurse)
    {
        StaffAlert::create([
            'sender_id' => auth()->id(),
            'sender_type' => 'receptionist',
            'recipient_id' => $nurse->user_id,
            'recipient_type' => 'nurse',
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->appointment_id,
            'alert_type' => 'Patient Assigned',
            'priority' => 'High',
            'alert_title' => '👤 New Patient Assigned to You',
            'alert_message' => "You have been assigned to patient {$appointment->patient->user->name} (Queue #{$appointment->queue_number}) for Dr. {$appointment->doctor->user->name}. Please check your queue.",
            'action_url' => route('nurse.queue-management'),
        ]);

        $appointment->update(['nurse_notified' => true]);
    }

    /**
     * Fallback: Send to all nurses
     */
    private function sendAlertToAllAvailableNurses(Appointment $appointment)
    {
        Log::warning("⚠️ FALLBACK: Broadcasting to all available nurses");

        $availableNurses = Nurse::whereIn('availability_status', ['Available', 'On Duty'])
            ->get();

        foreach ($availableNurses as $nurse) {
            StaffAlert::create([
                'sender_id' => auth()->id(),
                'sender_type' => 'receptionist',
                'recipient_id' => $nurse->user_id,
                'recipient_type' => 'nurse',
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->appointment_id,
                'alert_type' => 'Patient Checked In',
                'priority' => 'Normal',
                'alert_title' => '✅ Patient Checked In - No Nurse On Shift',
                'alert_message' => "Patient {$appointment->patient->user->name} has checked in but no nurses are on shift. Please attend if available.",
                'action_url' => route('nurse.patients.show', $appointment->patient_id),
            ]);
        }
    }

    /**
     * Complete assignment
     */
    public function completeAssignment($appointmentId, $nurseId)
    {
        $assignment = PatientNurseAssignment::where('appointment_id', $appointmentId)
            ->where('nurse_id', $nurseId)
            ->first();

        if (!$assignment) {
            return false;
        }

        DB::beginTransaction();

        try {
            $assignment->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $this->decrementNurseWorkload($nurseId);

            $completionTime = now()->diffInMinutes($assignment->accepted_at ?? $assignment->assigned_at);
            $this->updateNurseEfficiency($nurseId, $completionTime);

            NurseAssignmentLog::create([
                'appointment_id' => $appointmentId,
                'nurse_id' => $nurseId,
                'action' => 'completed',
                'action_at' => now(),
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    private function decrementNurseWorkload($nurseId)
    {
        $workload = NurseWorkloadTracking::where('nurse_id', $nurseId)->first();

        if ($workload) {
            $workload->decrement('current_patients');
            $workload->decrement('pending_vitals');
            $workload->update([
                'last_completed_at' => now(),
                'is_available' => $workload->current_patients < $workload->max_capacity,
            ]);
        }
    }

    private function updateNurseEfficiency($nurseId, $completionTimeMinutes)
    {
        $workload = NurseWorkloadTracking::where('nurse_id', $nurseId)->first();

        if ($workload) {
            $currentAvg = $workload->avg_completion_time_minutes;
            $newAvg = $currentAvg ? ($currentAvg + $completionTimeMinutes) / 2 : $completionTimeMinutes;

            $targetTime = 15;
            $efficiencyScore = max(0, min(100, ($targetTime / max($newAvg, 1)) * 100));

            $workload->update([
                'avg_completion_time_minutes' => round($newAvg),
                'efficiency_score' => round($efficiencyScore, 2),
            ]);
        }
    }

    /**
     * Reset daily workload
     */
    public function resetDailyWorkload()
    {
        NurseWorkloadTracking::query()->update([
            'total_today' => 0,
            'last_assignment_at' => null,
            'last_completed_at' => null,
        ]);
    }
}
