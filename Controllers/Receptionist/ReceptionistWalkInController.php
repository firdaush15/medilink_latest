<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\User;
use App\Services\QueueManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ReceptionistWalkInController extends Controller
{
    protected $queueService;

    public function __construct(QueueManagementService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Show walk-in patient registration form
     */
    public function create()
    {
        $patients = Patient::with('user')->get();
        $doctors = Doctor::with('user')
            ->where('availability_status', 'Available')
            ->get();

        return view('receptionist.receptionist_walkIn', compact('patients', 'doctors'));
    }

    /**
     * Store walk-in patient and create appointment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'urgency_level' => 'required|in:routine,urgent,emergency',
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'reason' => 'required|string|max:500',
            'walk_in_notes' => 'nullable|string',
            // Patient selection (either existing or new)
            'patient_id' => 'required_without_all:new_patient_name,new_patient_phone|nullable|exists:patients,patient_id',
            // New patient fields (only required if patient_id is empty)
            'new_patient_name' => 'nullable|required_without:patient_id|string|max:255',
            'new_patient_phone' => 'nullable|required_without:patient_id|string|max:20',
            'new_patient_ic' => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            // Handle new patient registration
            $patientId = $this->getOrCreatePatient($validated);

            // Create walk-in appointment
            $appointment = $this->createWalkInAppointment($patientId, $validated);

            // Auto check-in
            $appointment->checkInPatient(auth()->id());

            // Assign queue with priority
            $this->assignQueueWithPriority($appointment, $validated['urgency_level']);

            DB::commit();

            return redirect()
                ->route('receptionist.queue-ticket', $appointment->appointment_id)
                ->with('success', 'Walk-in patient registered successfully! ' .
                    ($validated['urgency_level'] === 'emergency' ? 'Priority queue assigned.' : ''));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Get existing patient or create new one
     */
    private function getOrCreatePatient(array $validated)
    {
        if (!empty($validated['patient_id'])) {
            return $validated['patient_id'];
        }

        // Quick register new patient for walk-in
        $user = User::create([
            'name' => $validated['new_patient_name'],
            'email' => 'temp_' . time() . '@medilink.temp', // Temporary email
            'password' => Hash::make(Str::random(32)),
            'role' => 'patient',
            'account_completed' => false,       // ✅ ADD THIS
            'registered_by_staff' => true,      // ✅ ADD THIS
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'phone_number' => $validated['new_patient_phone'],
            'gender' => 'Other',
            'date_of_birth' => '2000-01-01',
        ]);

        return $patient->patient_id;
    }

    /**
     * Create walk-in appointment
     */
    private function createWalkInAppointment($patientId, array $validated)
    {
        return Appointment::create([
            'patient_id' => $patientId,
            'doctor_id' => $validated['doctor_id'],
            'appointment_date' => today(),
            'appointment_time' => now()->format('H:i:s'),
            'status' => Appointment::STATUS_CONFIRMED,
            'reason' => $validated['reason'],
            'is_walk_in' => true,
            'urgency_level' => $validated['urgency_level'],
            'walk_in_notes' => $validated['walk_in_notes'],
        ]);
    }

    /**
     * Assign queue number with priority boosting
     */
    private function assignQueueWithPriority($appointment, $urgencyLevel)
    {
        $queueNumber = $this->queueService->assignQueueNumber($appointment->appointment_id);

        // Emergency patients go to front of queue
        if ($urgencyLevel === 'emergency') {
            $appointment->update(['queue_number' => 1]);

            // Shift other patients down
            Appointment::whereDate('appointment_date', today())
                ->where('doctor_id', $appointment->doctor_id)
                ->where('appointment_id', '!=', $appointment->appointment_id)
                ->whereIn('status', [
                    Appointment::STATUS_CHECKED_IN,
                    Appointment::STATUS_VITALS_PENDING,
                    Appointment::STATUS_VITALS_RECORDED,
                    Appointment::STATUS_READY_FOR_DOCTOR
                ])
                ->increment('queue_number');
        }
    }
}
