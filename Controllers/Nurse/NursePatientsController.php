<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\VitalRecord;
use App\Models\Appointment;
use App\Models\StaffAlert;
use App\Services\NurseAssignmentService; // ✅ ADD THIS
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NursePatientsController extends Controller
{
    protected $assignmentService; // ✅ ADD THIS

     public function __construct(NurseAssignmentService $assignmentService) // ✅ ADD THIS
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Display list of patients who have ARRIVED today
     */
    public function index(Request $request)
    {
        $nurse = auth()->user()->nurse;

        if (!$nurse) {
            abort(403, 'Nurse profile not found.');
        }

        $filter = $request->get('filter', 'all');
        $search = $request->get('search', '');

        // Query patients with today's appointments
        $patientsQuery = Patient::with([
            'user',
            'appointments' => function ($query) {
                $query->whereDate('appointment_date', Carbon::today())
                    ->whereIn('status', [
                        Appointment::STATUS_CHECKED_IN,
                        Appointment::STATUS_VITALS_PENDING,
                        Appointment::STATUS_VITALS_RECORDED,
                        Appointment::STATUS_READY_FOR_DOCTOR,
                        Appointment::STATUS_IN_CONSULTATION,
                    ])
                    ->with([
                        'doctor.user',
                        'latestVital',
                        'nurseWhoRecordedVitals.user',
                        'vitals' => function ($q) {
                            $q->whereDate('recorded_at', Carbon::today())->latest();
                        }
                    ]);
            }
        ])
        ->whereHas('appointments', function ($query) {
            $query->whereDate('appointment_date', Carbon::today())
                ->whereIn('status', [
                    Appointment::STATUS_CHECKED_IN,
                    Appointment::STATUS_VITALS_PENDING,
                    Appointment::STATUS_VITALS_RECORDED,
                    Appointment::STATUS_READY_FOR_DOCTOR,
                    Appointment::STATUS_IN_CONSULTATION,
                ]);
        });

        // Apply search filter
        if ($search) {
            $patientsQuery->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            })->orWhere('phone_number', 'LIKE', "%{$search}%")
                ->orWhere('patient_id', 'LIKE', "%{$search}%");
        }

        // Apply status filters
        switch ($filter) {
            case 'needs_vitals':
                $patientsQuery->whereHas('appointments', function ($query) {
                    $query->whereDate('appointment_date', Carbon::today())
                        ->where('status', Appointment::STATUS_CHECKED_IN);
                });
                break;

            case 'under_checkup':
                $patientsQuery->whereHas('appointments', function ($query) {
                    $query->whereDate('appointment_date', Carbon::today())
                        ->where('status', Appointment::STATUS_VITALS_PENDING);
                });
                break;

            case 'vitals_recorded':
                $patientsQuery->whereHas('appointments', function ($query) {
                    $query->whereDate('appointment_date', Carbon::today())
                        ->whereIn('status', [
                            Appointment::STATUS_VITALS_RECORDED,
                            Appointment::STATUS_READY_FOR_DOCTOR
                        ]);
                });
                break;

            case 'critical':
                $patientsQuery->whereHas('appointments', function ($query) {
                    $query->whereDate('appointment_date', Carbon::today())
                        ->where('critical_vitals_alert_sent', true);
                });
                break;

            case 'with_doctor':
                $patientsQuery->whereHas('appointments', function ($query) {
                    $query->whereDate('appointment_date', Carbon::today())
                        ->where('status', Appointment::STATUS_IN_CONSULTATION);
                });
                break;
        }

        // Get paginated results
        $patients = $patientsQuery->paginate(12);

        // Add computed properties
        $patients->getCollection()->transform(function ($patient) {
            $appointment = $patient->appointments->first();

            if ($appointment) {
                $patient->needs_vitals = $appointment->isCheckedIn();
                $patient->under_checkup = $appointment->status === Appointment::STATUS_VITALS_PENDING;
                $patient->vitals_recorded = $appointment->hasVitalsRecorded();
                $patient->ready_for_doctor = $appointment->isReadyForDoctor();
                $patient->with_doctor = $appointment->isWithDoctor();
                $patient->has_critical_vitals = $appointment->critical_vitals_alert_sent ?? false;
                $patient->latest_vital = $appointment->latestVital;
                $patient->today_appointment = $appointment;

                $patient->workflow_stage = $appointment->getCurrentStage();
                $patient->workflow_class = $appointment->getStageClass();
                $patient->next_action = $this->getNextAction($appointment);
            } else {
                $patient->needs_vitals = false;
                $patient->under_checkup = false;
                $patient->vitals_recorded = false;
                $patient->ready_for_doctor = false;
                $patient->with_doctor = false;
                $patient->has_critical_vitals = false;
                $patient->workflow_stage = 'No Appointment';
                $patient->workflow_class = 'badge-secondary';
                $patient->next_action = 'No appointment today';
            }

            return $patient;
        });

        // Calculate counts
        $needsVitalsCount = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('status', Appointment::STATUS_CHECKED_IN)
            ->count();

        $underCheckupCount = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('status', Appointment::STATUS_VITALS_PENDING)
            ->count();

        $vitalsRecordedCount = Appointment::whereDate('appointment_date', Carbon::today())
            ->whereIn('status', [
                Appointment::STATUS_VITALS_RECORDED,
                Appointment::STATUS_READY_FOR_DOCTOR
            ])
            ->count();

        $criticalVitalsCount = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('critical_vitals_alert_sent', true)
            ->count();

        $withDoctorCount = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('status', Appointment::STATUS_IN_CONSULTATION)
            ->count();

        $completedCount = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('status', Appointment::STATUS_COMPLETED)
            ->count();

        $checkedInCount = Appointment::whereDate('appointment_date', Carbon::today())
            ->whereIn('status', [
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
                Appointment::STATUS_VITALS_RECORDED,
                Appointment::STATUS_READY_FOR_DOCTOR,
                Appointment::STATUS_IN_CONSULTATION,
            ])
            ->distinct('patient_id')
            ->count('patient_id');

        $totalPatientsCount = $checkedInCount;

        return view('nurse.nurse_patients', compact(
            'patients',
            'filter',
            'search',
            'criticalVitalsCount',
            'needsVitalsCount',
            'underCheckupCount',
            'vitalsRecordedCount',
            'completedCount',
            'checkedInCount',
            'withDoctorCount',
            'totalPatientsCount'
        ));
    }

    /**
     * Start vitals recording (marks as "Under Checkup")
     */
    public function startVitalsRecording($appointmentId)
    {
        $nurse = auth()->user()->nurse;
        $appointment = Appointment::findOrFail($appointmentId);

        if ($appointment->status !== Appointment::STATUS_CHECKED_IN) {
            return response()->json(['error' => 'Patient must be checked in first'], 400);
        }

        try {
            $appointment->startVitalsRecording($nurse->nurse_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Started vitals recording - patient marked as under checkup'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

public function storeVitals(Request $request)
    {
        $nurse = auth()->user()->nurse;

        if (!$nurse) {
            return redirect()->back()->with('error', 'Nurse profile not found.');
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,patient_id',
            'appointment_id' => 'nullable|exists:appointments,appointment_id',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'blood_pressure' => 'nullable|string|max:20',
            'heart_rate' => 'nullable|integer|min:30|max:200',
            'respiratory_rate' => 'nullable|integer|min:8|max:40',
            'oxygen_saturation' => 'nullable|integer|min:50|max:100',
            'weight' => 'nullable|numeric|min:1|max:500',
            'height' => 'nullable|numeric|min:50|max:250',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Step 1: Create vital record
            $vital = VitalRecord::create([
                'patient_id' => $validated['patient_id'],
                'nurse_id' => $nurse->nurse_id,
                'appointment_id' => $validated['appointment_id'] ?? null,
                'temperature' => $validated['temperature'] ?? null,
                'blood_pressure' => $validated['blood_pressure'] ?? null,
                'heart_rate' => $validated['heart_rate'] ?? null,
                'respiratory_rate' => $validated['respiratory_rate'] ?? null,
                'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'height' => $validated['height'] ?? null,
                'recorded_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $vital->checkCriticalStatus();

            // Step 2: Update appointment workflow
            if ($validated['appointment_id']) {
                $appointment = Appointment::with(['doctor.user', 'patient.user'])
                    ->find($validated['appointment_id']);

                if ($appointment) {
                    if ($appointment->isCheckedIn()) {
                        $appointment->startVitalsRecording($nurse->nurse_id);
                    }
                    
                    if ($appointment->status === Appointment::STATUS_VITALS_PENDING || 
                        $appointment->status === Appointment::STATUS_CHECKED_IN) {
                        $appointment->completeVitalsRecording($nurse->nurse_id);
                    }

                    $this->acknowledgeCheckInAlert($appointment);
                    $this->createVitalsAlertForDoctor($appointment, $vital, $vital->is_critical);

                    if (!$vital->is_critical && $appointment->status === Appointment::STATUS_VITALS_RECORDED) {
                        $appointment->markReadyForDoctor($nurse->nurse_id);
                    }

                    // ✅ STEP 3: COMPLETE ASSIGNMENT
                    $this->assignmentService->completeAssignment(
                        $appointment->appointment_id,
                        $nurse->nurse_id
                    );
                }
            }

            DB::commit();

            $message = 'Vital signs recorded successfully.';

            if ($vital->is_critical) {
                $message .= ' ⚠️ CRITICAL vitals detected - Doctor has been alerted immediately!';
            } else {
                $message .= ' Patient is now ready for doctor consultation.';
            }

            return redirect()->route('nurse.patients')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to store vitals: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to record vitals: ' . $e->getMessage());
        }
    }

    /**
     * 🔔 ACKNOWLEDGE CHECK-IN ALERT (mark as handled)
     */
    protected function acknowledgeCheckInAlert(Appointment $appointment)
    {
        StaffAlert::where('appointment_id', $appointment->appointment_id)
            ->where('recipient_id', auth()->id())
            ->where('recipient_type', 'nurse')
            ->where('alert_type', 'Patient Checked In')
            ->where('is_acknowledged', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'is_acknowledged' => true,
                'acknowledged_at' => now(),
            ]);
    }

    /**
     * 🔔 CREATE VITALS ALERT FOR DOCTOR
     */
    protected function createVitalsAlertForDoctor(Appointment $appointment, VitalRecord $vital, bool $isCritical)
    {
        $priority = $isCritical ? 'Critical' : 'Normal';
        
        if ($isCritical) {
            // CRITICAL VITALS ALERT
            $title = '🚨 CRITICAL: Abnormal Vital Signs Detected';
            $message = "URGENT: Patient {$appointment->patient->user->name} (Queue #{$appointment->formatted_queue_number}) has CRITICAL vital signs:\n\n" .
                       "🌡️ Temperature: {$vital->temperature}°C\n" .
                       "💓 Heart Rate: {$vital->heart_rate} BPM\n" .
                       "🫁 Blood Pressure: {$vital->blood_pressure}\n" .
                       "💨 SpO2: {$vital->oxygen_saturation}%\n\n" .
                       "⚠️ IMMEDIATE ATTENTION REQUIRED";
        } else {
            // NORMAL VITALS - PATIENT READY
            $title = '👨‍⚕️ Patient Ready for Consultation';
            $message = "Patient {$appointment->patient->user->name} (Queue #{$appointment->formatted_queue_number}) vitals recorded and verified. Ready for your consultation.\n\n" .
                       "Vitals Summary:\n" .
                       "🌡️ Temp: {$vital->temperature}°C | 💓 HR: {$vital->heart_rate} BPM\n" .
                       "🫁 BP: {$vital->blood_pressure} | 💨 SpO2: {$vital->oxygen_saturation}%";
        }

        StaffAlert::create([
            'sender_id' => auth()->id(),
            'sender_type' => 'nurse',
            'recipient_id' => $appointment->doctor->user_id,
            'recipient_type' => 'doctor',
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->appointment_id,
            'alert_type' => $isCritical ? 'Critical Vitals' : 'Patient Ready',
            'priority' => $priority,
            'alert_title' => $title,
            'alert_message' => $message,
            'action_url' => route('doctor.patients.show', $appointment->patient_id),
        ]);

        Log::info("Vitals alert sent to Dr. {$appointment->doctor->user->name} for patient {$appointment->patient->user->name} (Critical: " . ($isCritical ? 'YES' : 'NO') . ")");
    }

    /**
     * Show individual patient details
     */
    public function show($id)
    {
        $patient = Patient::with([
            'user',
            'appointments' => function ($query) {
                $query->with(['doctor.user', 'nurseWhoRecordedVitals.user'])
                    ->orderBy('appointment_date', 'desc')
                    ->limit(10);
            }
        ])->findOrFail($id);

        // Get vital records history
        $vitalRecords = VitalRecord::where('patient_id', $id)
            ->with('nurse.user')
            ->orderBy('recorded_at', 'desc')
            ->paginate(20);

        // Get medical records
        $medicalRecords = $patient->medicalRecords()
            ->with('doctor.user')
            ->orderBy('record_date', 'desc')
            ->limit(10)
            ->get();

        return view('nurse.patient_details', compact(
            'patient',
            'vitalRecords',
            'medicalRecords'
        ));
    }

    /**
     * Get next action text for patient
     */
    private function getNextAction($appointment)
    {
        return match ($appointment->status) {
            Appointment::STATUS_CHECKED_IN => 'Needs vitals recording',
            Appointment::STATUS_VITALS_PENDING => '🩺 Under checkup - Recording vitals in progress',
            Appointment::STATUS_VITALS_RECORDED => 'Vitals recorded, awaiting verification',
            Appointment::STATUS_READY_FOR_DOCTOR => 'Waiting for doctor',
            Appointment::STATUS_IN_CONSULTATION => 'Currently in consultation',
            default => 'Status unknown',
        };
    }
}