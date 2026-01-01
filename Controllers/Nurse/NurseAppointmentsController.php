<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\VitalRecord;
use Carbon\Carbon;

class NurseAppointmentsController extends Controller
{
    public function index(Request $request)
    {
        $nurse = auth()->user()->nurse;

        if (!$nurse) {
            abort(403, 'Nurse profile not found.');
        }

        // Get filter parameters
        $filterDoctor = $request->get('doctor_id', 'all');
        $filterStatus = $request->get('status', 'all');
        $timeSlot = $request->get('time_slot', 'all');

        // Build query for patients who have ARRIVED
        $appointmentsQuery = Appointment::with([
            'patient.user', 
            'doctor.user',
            'receptionistWhoCheckedIn',
            'nurseWhoRecordedVitals.user',
            'vitals' => function($query) {
                $query->whereDate('recorded_at', Carbon::today())->latest();
            }
        ])
        ->whereDate('appointment_date', Carbon::today())
        ->whereIn('status', [
            Appointment::STATUS_CHECKED_IN,
            Appointment::STATUS_VITALS_PENDING,
            Appointment::STATUS_VITALS_RECORDED,
            Appointment::STATUS_READY_FOR_DOCTOR,
            Appointment::STATUS_IN_CONSULTATION,
        ]);

        // Apply doctor filter
        if ($filterDoctor !== 'all') {
            $appointmentsQuery->where('doctor_id', $filterDoctor);
        }

        // Apply time slot filter
        if ($timeSlot !== 'all') {
            switch ($timeSlot) {
                case 'morning':
                    $appointmentsQuery->whereTime('appointment_time', '>=', '06:00:00')
                                      ->whereTime('appointment_time', '<', '12:00:00');
                    break;
                case 'afternoon':
                    $appointmentsQuery->whereTime('appointment_time', '>=', '12:00:00')
                                      ->whereTime('appointment_time', '<', '18:00:00');
                    break;
                case 'evening':
                    $appointmentsQuery->whereTime('appointment_time', '>=', '18:00:00')
                                      ->whereTime('appointment_time', '<=', '23:59:59');
                    break;
            }
        }

        // Apply status filters
        if ($filterStatus === 'waiting_for_nurse') {
            $appointmentsQuery->where('status', Appointment::STATUS_CHECKED_IN);
        } elseif ($filterStatus === 'under_checkup') {
            $appointmentsQuery->whereIn('status', [
                Appointment::STATUS_VITALS_PENDING,
                Appointment::STATUS_VITALS_RECORDED
            ]);
        } elseif ($filterStatus === 'ready_for_doctor') {
            $appointmentsQuery->where('status', Appointment::STATUS_READY_FOR_DOCTOR)
                              ->whereNull('consultation_started_at');
        } elseif ($filterStatus === 'with_doctor') {
            $appointmentsQuery->where('status', Appointment::STATUS_IN_CONSULTATION);
        }

        // Paginate appointments
        $appointments = $appointmentsQuery
            ->orderByRaw("
                CASE 
                    WHEN status = 'checked_in' THEN 1
                    WHEN status = 'vitals_pending' THEN 2
                    WHEN status = 'vitals_recorded' THEN 3
                    WHEN status = 'ready_for_doctor' THEN 4
                    WHEN status = 'in_consultation' THEN 5
                END
            ")
            ->orderBy('appointment_time', 'asc')
            ->paginate(15);

        // Add computed properties including waiting time
        $appointments->getCollection()->transform(function($appointment) {
            $appointment->has_arrived = $appointment->hasArrived();
            $appointment->needs_vitals = $appointment->isCheckedIn();
            $appointment->has_vitals = $appointment->hasVitalsRecorded();
            $appointment->is_ready_for_doctor = $appointment->isReadyForDoctor();
            $appointment->with_doctor = $appointment->isWithDoctor();
            
            $appointment->current_stage = $appointment->getCurrentStage();
            $appointment->stage_class = $appointment->getStageClass();
            
            // ✅ NEW: Calculate waiting time
            if ($appointment->arrived_at) {
                $appointment->waiting_time = $appointment->arrived_at->diffForHumans(null, true);
                $appointment->waiting_minutes = now()->diffInMinutes($appointment->arrived_at);
                $appointment->is_delayed = $appointment->waiting_minutes > 30;
            } else {
                $appointment->waiting_time = null;
                $appointment->waiting_minutes = 0;
                $appointment->is_delayed = false;
            }
            
            // ✅ NEW: Get latest vital signs
            $appointment->latest_vital_summary = $this->getLatestVitalSummary($appointment);
            
            return $appointment;
        });

        // Get list of doctors for filter
        $doctors = Doctor::with('user')
            ->whereHas('appointments', function($query) {
                $query->whereDate('appointment_date', Carbon::today())
                      ->whereIn('status', [
                          Appointment::STATUS_CHECKED_IN,
                          Appointment::STATUS_VITALS_PENDING,
                          Appointment::STATUS_VITALS_RECORDED,
                          Appointment::STATUS_READY_FOR_DOCTOR,
                          Appointment::STATUS_IN_CONSULTATION,
                      ]);
            })
            ->get();

        // Calculate counts
        $waitingForNurseCount = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('status', Appointment::STATUS_CHECKED_IN)
            ->count();

        $underCheckupCount = Appointment::whereDate('appointment_date', Carbon::today())
            ->whereIn('status', [
                Appointment::STATUS_VITALS_PENDING,
                Appointment::STATUS_VITALS_RECORDED
            ])
            ->count();

        $readyForDoctorCount = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('status', Appointment::STATUS_READY_FOR_DOCTOR)
            ->whereNull('consultation_started_at')
            ->count();

        $withDoctorCount = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('status', Appointment::STATUS_IN_CONSULTATION)
            ->count();

        return view('nurse.nurse_appointments', compact(
            'appointments',
            'doctors',
            'filterDoctor',
            'filterStatus',
            'timeSlot',
            'waitingForNurseCount',
            'underCheckupCount',
            'readyForDoctorCount',
            'withDoctorCount'
        ));
    }

    /**
     * ✅ NEW: Quick record vitals from appointments page
     */
    public function quickRecordVitals(Request $request, $appointmentId)
    {
        $nurse = auth()->user()->nurse;
        $appointment = Appointment::findOrFail($appointmentId);

        $validated = $request->validate([
            'temperature' => 'nullable|numeric|between:35,42',
            'blood_pressure' => 'nullable|string',
            'heart_rate' => 'nullable|integer|between:40,200',
            'respiratory_rate' => 'nullable|integer|between:10,40',
            'oxygen_saturation' => 'nullable|integer|between:70,100',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        try {
            \DB::beginTransaction();

            // Create vital record
            $vital = VitalRecord::create([
                'patient_id' => $appointment->patient_id,
                'nurse_id' => $nurse->nurse_id,
                'appointment_id' => $appointmentId,
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

            // Check for critical vitals
            $vital->checkCriticalStatus();

            // Update appointment workflow
            if ($appointment->status === Appointment::STATUS_CHECKED_IN) {
                $appointment->startVitalsRecording($nurse->nurse_id);
            }
            
            $appointment->completeVitalsRecording($nurse->nurse_id);

            // Auto-mark ready if not critical
            if (!$vital->is_critical) {
                $appointment->markReadyForDoctor($nurse->nurse_id);
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => $vital->is_critical 
                    ? 'Vitals recorded - CRITICAL values detected!'
                    : 'Vitals recorded successfully. Patient marked as ready.',
                'is_critical' => $vital->is_critical,
                'redirect' => route('nurse.appointments')
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark patient as ready for doctor (after vitals recorded)
     */
    public function markReadyForDoctor($id)
    {
        $appointment = Appointment::findOrFail($id);

        // Validation
        if ($appointment->status !== Appointment::STATUS_VITALS_RECORDED) {
            return redirect()->back()->with('error', 'Cannot mark as ready. Vitals must be recorded first.');
        }

        // Check if vitals exist today
        $hasVitals = VitalRecord::where('appointment_id', $id)
            ->whereDate('recorded_at', today())
            ->exists();

        if (!$hasVitals) {
            return redirect()->back()->with('error', 'Cannot mark as ready. No vitals recorded today.');
        }

        try {
            $appointment->markReadyForDoctor(auth()->user()->nurse->nurse_id);
            return redirect()->back()->with('success', 'Patient marked as ready for doctor!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ NEW: Refresh status counts for real-time updates
     */
    public function refreshCounts()
    {
        return response()->json([
            'waiting_for_nurse' => Appointment::whereDate('appointment_date', Carbon::today())
                ->where('status', Appointment::STATUS_CHECKED_IN)
                ->count(),
            'under_checkup' => Appointment::whereDate('appointment_date', Carbon::today())
                ->whereIn('status', [
                    Appointment::STATUS_VITALS_PENDING,
                    Appointment::STATUS_VITALS_RECORDED
                ])
                ->count(),
            'ready_for_doctor' => Appointment::whereDate('appointment_date', Carbon::today())
                ->where('status', Appointment::STATUS_READY_FOR_DOCTOR)
                ->count(),
            'with_doctor' => Appointment::whereDate('appointment_date', Carbon::today())
                ->where('status', Appointment::STATUS_IN_CONSULTATION)
                ->count(),
        ]);
    }

    /**
     * ✅ NEW: Helper to get latest vital summary
     */
    private function getLatestVitalSummary($appointment)
    {
        $vital = $appointment->vitals->first();
        
        if (!$vital) {
            return null;
        }

        return [
            'temperature' => $vital->temperature ? $vital->temperature . '°C' : 'N/A',
            'blood_pressure' => $vital->blood_pressure ?? 'N/A',
            'heart_rate' => $vital->heart_rate ? $vital->heart_rate . ' bpm' : 'N/A',
            'oxygen_saturation' => $vital->oxygen_saturation ? $vital->oxygen_saturation . '%' : 'N/A',
            'is_critical' => $vital->is_critical,
            'recorded_time' => $vital->recorded_at->diffForHumans(),
        ];
    }
}