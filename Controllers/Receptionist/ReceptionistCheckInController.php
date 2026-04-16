<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\StaffAlert;
use App\Models\Nurse;
use App\Models\PatientNurseAssignment;
use App\Models\NurseWorkloadTracking;
use App\Models\StaffShift;
use App\Models\User;
use App\Services\QueueManagementService;
use App\Services\NurseAssignmentService;
use App\Services\CheckInValidationService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReceptionistCheckInController extends Controller
{
    protected $queueService;
    protected $nurseAssignmentService;
    protected $checkInValidationService;

    public function __construct(
        QueueManagementService $queueService,
        NurseAssignmentService $nurseAssignmentService,
        CheckInValidationService $checkInValidationService
    ) {
        $this->queueService             = $queueService;
        $this->nurseAssignmentService   = $nurseAssignmentService;
        $this->checkInValidationService = $checkInValidationService;
    }

    /**
     * Display check-in page with today's appointments
     */
    public function index()
    {
        $appointments = Appointment::with(['patient.user', 'doctor.user', 'receptionistWhoCheckedIn', 'assignedNurse.user'])
            ->whereDate('appointment_date', today())
            ->whereIn('status', [
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
                Appointment::STATUS_VITALS_RECORDED,
                Appointment::STATUS_READY_FOR_DOCTOR,
                Appointment::STATUS_IN_CONSULTATION,
            ])
            ->orderBy('queue_priority_score', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->get();

        $waitingPatients = $appointments->filter(function ($appointment) {
            return in_array($appointment->status, [
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
                Appointment::STATUS_VITALS_RECORDED,
                Appointment::STATUS_READY_FOR_DOCTOR,
            ]) && $appointment->consultation_started_at === null;
        })->sortBy('queue_priority_score');

        $waitingCount = $waitingPatients->count();

        // Calculate average wait time
        $avgWaitTime = null;
        if ($waitingCount > 0) {
            $totalWaitMinutes = $waitingPatients->sum(function ($appointment) {
                if ($appointment->arrived_at) {
                    return $appointment->arrived_at->diffInMinutes(now());
                }
                return 0;
            });
            $avgWaitTime = round($totalWaitMinutes / $waitingCount);
        }

        // Get operating hours info for display
        $operatingHours = $this->checkInValidationService->getOperatingHoursInfo();

        return view('receptionist.receptionist_checkIn', compact(
            'appointments',
            'waitingPatients',
            'waitingCount',
            'avgWaitTime',
            'operatingHours'
        ));
    }

    /**
     * Process patient check-in.
     *
     * Flow:
     *  1. Validate check-in eligibility.
     *  2. Check patient in (sets arrived_at, status → checked_in).
     *  3. Assign queue priority score.
     *  4. Assign display queue number.
     *  5. Smart nurse assignment (shift-aware).
     *  6. Alert the assigned nurse that a patient has been assigned to them.
     */
    public function process(Request $request, $appointmentId)
    {
        $appointment = Appointment::with(['patient.user', 'doctor.user'])
            ->findOrFail($appointmentId);

        // Step 1: Validate check-in eligibility
        $validation = $this->checkInValidationService->validateCheckIn($appointment);

        if (!$validation['valid']) {
            return back()->withErrors(['error' => $validation['error']]);
        }

        try {
            DB::beginTransaction();

            // Step 2: Check in patient
            $appointment->checkInPatient(auth()->id());
            Log::info("Patient {$appointment->patient->user->name} checked in by " . auth()->user()->name);

            // Step 3: Assign queue priority
            $priorityScore = $this->queueService->assignPriority($appointmentId);
            Log::info("Priority score assigned: {$priorityScore}");

            // Step 4: Refresh appointment data after check-in changes
            $appointment->refresh();

            // Step 5: Assign display queue number
            $this->assignDisplayQueueNumber($appointment);

            // Step 6: Smart nurse assignment (shift-aware)
            $this->performNurseAssignment($appointment);

            DB::commit();

            // Step 7: Reload to get the final nurse assignment
            $appointment->refresh();

            // Step 8: Alert the assigned nurse (outside the transaction so it won't rollback)
            if ($appointment->assignedNurse) {
                $this->alertAssignedNurse($appointment);
            }

            // Step 9: Build success message and redirect to queue ticket
            $message = $this->buildSuccessMessage($appointment);

            return redirect()
                ->route('receptionist.queue-ticket', $appointment->appointment_id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Check-in failed for appointment ' . $appointmentId . ': ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return back()->withErrors([
                'error' => 'Check-in failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Alert the assigned nurse that a patient has been checked in and assigned to them.
     * This is how the nurse knows to go record vitals.
     */
    protected function alertAssignedNurse(Appointment $appointment): void
    {
        $nurse     = $appointment->assignedNurse;
        $patient   = $appointment->patient;
        $doctor    = $appointment->doctor;

        if (!$nurse || !$nurse->user_id) {
            return;
        }

        // Check if we already sent an alert for this appointment to avoid duplicates
        $alreadyAlerted = StaffAlert::where('appointment_id', $appointment->appointment_id)
            ->where('recipient_id', $nurse->user_id)
            ->where('recipient_type', 'nurse')
            ->where('alert_type', 'Patient Assigned')
            ->exists();

        if ($alreadyAlerted) {
            Log::info("Nurse {$nurse->user->name} already alerted for appointment {$appointment->appointment_id}, skipping.");
            return;
        }

        $isOnShift = $this->isNurseOnShift($nurse);

        $alertTitle   = $isOnShift
            ? '👤 New Patient Assigned to You'
            : '👤 URGENT: Patient Assigned (Off-Shift)';

        $alertMessage = "Patient {$patient->user->name} has been checked in and assigned to you "
                      . "for Dr. {$doctor->user->name}. "
                      . "Please record vital signs as soon as possible.";

        if (!$isOnShift) {
            $alertMessage .= " NOTE: You are not on your scheduled shift — please attend urgently.";
        }

        StaffAlert::create([
            'sender_id'      => auth()->id(),
            'sender_type'    => 'receptionist',
            'recipient_id'   => $nurse->user_id,
            'recipient_type' => 'nurse',
            'patient_id'     => $patient->patient_id,
            'appointment_id' => $appointment->appointment_id,
            'alert_type'     => 'Patient Assigned',
            'priority'       => $isOnShift ? 'High' : 'Urgent',
            'alert_title'    => $alertTitle,
            'alert_message'  => $alertMessage,
            'action_url'     => route('nurse.queue-management'),
        ]);

        // Mark the appointment as nurse notified
        $appointment->update(['nurse_notified' => true]);

        Log::info("Patient Assigned alert sent to nurse {$nurse->user->name} for patient {$patient->user->name}.");
    }

    /**
     * Perform nurse assignment with fallback logic
     */
    private function performNurseAssignment(Appointment $appointment): void
    {
        Log::info("=== STARTING SHIFT-AWARE NURSE ASSIGNMENT ===");
        Log::info("Appointment ID: {$appointment->appointment_id}");
        Log::info("Doctor: {$appointment->doctor->user->name}");
        Log::info("Patient: {$appointment->patient->user->name}");
        Log::info("Current Time: " . now()->format('Y-m-d H:i:s'));

        // Try primary assignment (shift-aware)
        $nurseAssignment = $this->nurseAssignmentService->assignNurseToAppointment($appointment);

        // If primary assignment fails, try fallback
        if (!$nurseAssignment) {
            Log::warning("⚠️ Primary shift-aware assignment failed. Attempting fallback...");
            $this->handleAssignmentFailure($appointment);
        } else {
            Log::info("✅ Primary nurse assignment successful");
        }
    }

    /**
     * Handle assignment failure with fallback strategies
     */
    private function handleAssignmentFailure(Appointment $appointment): void
    {
        // Strategy 1: Try any available nurse (even if not on shift)
        $fallbackNurse = Nurse::whereIn('availability_status', ['Available', 'On Duty'])
            ->whereHas('workload', function ($q) {
                $q->where('is_available', true)
                    ->whereRaw('current_patients < max_capacity');
            })
            ->first();

        if ($fallbackNurse) {
            $this->assignFallbackNurse($appointment, $fallbackNurse);
            return;
        }

        // Strategy 2: Emergency - alert all nurses and admins
        Log::error("❌ NO NURSES AVAILABLE (ALL STRATEGIES EXHAUSTED)");
        $this->sendEmergencyAlerts($appointment);
    }

    /**
     * Assign fallback nurse with proper tracking
     */
    private function assignFallbackNurse(Appointment $appointment, Nurse $nurse): void
    {
        Log::warning("🔧 FALLBACK: Assigning to nurse {$nurse->user->name}");

        $isOnShift      = $this->isNurseOnShift($nurse);
        $assignmentMethod = $isOnShift ? 'fallback' : 'manual';

        // Create assignment record
        PatientNurseAssignment::create([
            'appointment_id'   => $appointment->appointment_id,
            'patient_id'       => $appointment->patient_id,
            'nurse_id'         => $nurse->nurse_id,
            'assignment_method'=> $assignmentMethod,
            'assigned_at'      => now(),
            'assigned_by'      => auth()->id(),
            'status'           => 'accepted',
            'accepted_at'      => now(),
        ]);

        // Update appointment
        $appointment->update([
            'assigned_nurse_id' => $nurse->nurse_id,
            'nurse_assigned_at' => now(),
            'nurse_accepted_at' => now(),
            'nurse_notified'    => false,
        ]);

        // Update nurse workload
        $this->updateNurseWorkload($nurse->nurse_id);

        Log::info("✅ FALLBACK ASSIGNMENT SUCCESSFUL" . ($isOnShift ? " (ON SHIFT)" : " (OFF-SHIFT)"));
    }

    /**
     * Update nurse workload tracking
     */
    private function updateNurseWorkload(int $nurseId): void
    {
        $workload = NurseWorkloadTracking::firstOrCreate(
            ['nurse_id' => $nurseId],
            [
                'current_patients' => 0,
                'pending_vitals'   => 0,
                'total_today'      => 0,
                'max_capacity'     => 5,
                'is_available'     => true,
                'current_status'   => 'available',
                'efficiency_score' => 100,
            ]
        );

        $workload->increment('current_patients');
        $workload->increment('pending_vitals');
        $workload->increment('total_today');

        $workload->update([
            'last_assignment_at' => now(),
            'is_available'       => $workload->current_patients < $workload->max_capacity,
        ]);

        Log::info("Nurse workload updated: {$workload->current_patients}/{$workload->max_capacity}");
    }

    /**
     * Send emergency alerts to all nurses and admins when no nurses are available
     */
    private function sendEmergencyAlerts(Appointment $appointment): void
    {
        Log::critical("🚨 EMERGENCY: No nurses available for appointment {$appointment->appointment_id}");

        // Alert all nurses
        $allNurses = Nurse::all();
        foreach ($allNurses as $nurse) {
            StaffAlert::create([
                'sender_id'      => auth()->id(),
                'sender_type'    => 'system',
                'recipient_id'   => $nurse->user_id,
                'recipient_type' => 'nurse',
                'patient_id'     => $appointment->patient_id,
                'appointment_id' => $appointment->appointment_id,
                'alert_type'     => 'Emergency',
                'priority'       => 'Critical',
                'alert_title'    => '🚨 EMERGENCY: Patient Needs Immediate Attention',
                'alert_message'  => "Patient {$appointment->patient->user->name} checked in for "
                                  . "Dr. {$appointment->doctor->user->name}. "
                                  . "NO NURSES ON SHIFT — immediate attention required!",
                'action_url'     => route('nurse.patients.show', $appointment->patient_id),
            ]);
        }

        // Alert all admins — dynamically, no hardcoded IDs
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            StaffAlert::create([
                'sender_id'      => auth()->id(),
                'sender_type'    => 'system',
                'recipient_id'   => $admin->id,
                'recipient_type' => 'admin',
                'patient_id'     => $appointment->patient_id,
                'appointment_id' => $appointment->appointment_id,
                'alert_type'     => 'Staffing Crisis',
                'priority'       => 'Critical',
                'alert_title'    => '🚨 STAFFING CRISIS: No Nurses Available',
                'alert_message'  => "Patient {$appointment->patient->user->name} checked in but "
                                  . "NO nurses are available or on shift. "
                                  . "Immediate staffing intervention required!",
                'action_url'     => route('admin.shifts.index'),
            ]);
        }

        Log::info("Emergency alerts sent to {$allNurses->count()} nurses and {$admins->count()} admins");
    }

    /**
     * Build success message based on assignment result
     */
    private function buildSuccessMessage(Appointment $appointment): string
    {
        $message = '✓ Patient checked in successfully!';

        if ($appointment->assignedNurse) {
            $nurseName = $appointment->assignedNurse->user->name;
            $isOnShift = $this->isNurseOnShift($appointment->assignedNurse);

            if ($isOnShift) {
                $message .= " Nurse {$nurseName} (currently on shift) has been assigned and notified.";
            } else {
                $message .= " Nurse {$nurseName} has been assigned (emergency off-shift assignment).";
            }
        } else {
            $message .= " ⚠️ No nurses currently available — emergency alerts sent to all staff and admin.";
        }

        return $message;
    }

    /**
     * Assign sequential display queue number
     */
    protected function assignDisplayQueueNumber(Appointment $appointment): void
    {
        $position = Appointment::whereDate('appointment_date', today())
            ->where('doctor_id', $appointment->doctor_id)
            ->whereNotNull('queue_priority_score')
            ->whereIn('status', [
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
                Appointment::STATUS_VITALS_RECORDED,
                Appointment::STATUS_READY_FOR_DOCTOR,
            ])
            ->where('queue_priority_score', '<=', $appointment->queue_priority_score)
            ->count();

        $appointment->update(['queue_number' => $position]);

        Log::info("Display queue number assigned: #{$position} for appointment {$appointment->appointment_id}");
    }

    /**
     * Check if nurse is currently on an active shift
     */
    private function isNurseOnShift(Nurse $nurse): bool
    {
        $currentTime = now()->format('H:i:s');

        return StaffShift::where('user_id', $nurse->user_id)
            ->where('shift_date', today())
            ->where('status', 'checked_in')
            ->whereTime('start_time', '<=', $currentTime)
            ->whereTime('end_time', '>=', $currentTime)
            ->exists();
    }

    /**
     * Show printable queue ticket
     */
    public function showQueueTicket($appointmentId)
    {
        $appointment = Appointment::with(['patient.user', 'doctor.user', 'assignedNurse.user'])
            ->findOrFail($appointmentId);

        // Ensure patient has been checked in
        if (!$appointment->arrived_at) {
            return redirect()
                ->route('receptionist.check-in')
                ->withErrors(['error' => 'Patient has not been checked in yet.']);
        }

        return view('receptionist.queue_ticket', compact('appointment'));
    }

    /**
     * Public queue display screen (for TV/Monitor)
     */
    public function queueDisplay()
    {
        // Get the patient currently being served
        $nowServing = Appointment::with(['patient.user', 'doctor.user'])
            ->whereDate('appointment_date', today())
            ->where('status', Appointment::STATUS_IN_CONSULTATION)
            ->orderBy('consultation_started_at', 'desc')
            ->first();

        // Get waiting queue (next 10 patients) - ordered by priority score
        $waitingQueue = Appointment::with(['patient.user', 'doctor.user'])
            ->whereDate('appointment_date', today())
            ->whereIn('status', [
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
                Appointment::STATUS_VITALS_RECORDED,
                Appointment::STATUS_READY_FOR_DOCTOR,
            ])
            ->whereNull('consultation_started_at')
            ->orderBy('queue_priority_score', 'asc')
            ->take(10)
            ->get();

        return view('queue.display', compact('nowServing', 'waitingQueue'));
    }

    /**
     * Get queue status for a doctor (AJAX endpoint)
     */
    public function getQueueStatus(Request $request, $doctorId)
    {
        $queue = $this->queueService->getQueueForDoctor($doctorId);

        return response()->json([
            'success'       => true,
            'queue'         => $queue,
            'total_waiting' => count($queue),
        ]);
    }

    /**
     * Get patient's queue position (AJAX endpoint)
     */
    public function getPatientQueue($appointmentId)
    {
        $queueInfo = $this->queueService->getPatientQueueInfo($appointmentId);

        return response()->json([
            'success'    => true,
            'queue_info' => $queueInfo,
        ]);
    }

    /**
     * Search appointments for check-in (AJAX endpoint)
     */
    public function searchForCheckIn(Request $request)
    {
        $query = $request->get('query');

        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter at least 2 characters'
            ]);
        }

        $appointments = Appointment::with(['patient.user', 'doctor.user'])
            ->whereDate('appointment_date', today())
            ->whereIn('status', [
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
                Appointment::STATUS_VITALS_RECORDED,
                Appointment::STATUS_READY_FOR_DOCTOR,
            ])
            ->where(function ($q) use ($query) {
                $q->whereHas('patient.user', function ($subQ) use ($query) {
                    $subQ->where('name', 'LIKE', "%{$query}%");
                })
                ->orWhereHas('patient', function ($subQ) use ($query) {
                    $subQ->where('patient_id', 'LIKE', "%{$query}%");
                })
                ->orWhereHas('patient', function ($subQ) use ($query) {
                    $subQ->where('phone_number', 'LIKE', "%{$query}%");
                });
            })
            ->orderBy('appointment_time', 'asc')
            ->limit(10)
            ->get();

        return response()->json([
            'success'      => true,
            'appointments' => $appointments
        ]);
    }
}