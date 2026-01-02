<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\User;
use App\Services\QueueManagementService;
use App\Helpers\PhoneHelper;
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

    public function create()
    {
        $patients = Patient::with('user')->get();
        $doctors = Doctor::with('user')
            ->where('availability_status', 'Available')
            ->get();

        return view('receptionist.receptionist_walkIn', compact('patients', 'doctors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'urgency_level' => 'required|in:routine,urgent,emergency',
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'reason' => 'required|string|max:500',
            'walk_in_notes' => 'nullable|string',
            'patient_id' => 'nullable|exists:patients,patient_id',
            'new_patient_name' => 'nullable|required_without:patient_id|string|max:255',
            'new_patient_phone' => 'nullable|required_without:patient_id|string|max:20',
            'new_patient_ic' => 'nullable|required_without:patient_id|string|unique:patients,ic_number',
            'new_patient_email' => 'nullable|email|unique:users,email',
        ]);

        try {
            DB::beginTransaction();

            // 1. Get or Create Patient
            $patientId = $this->getOrCreatePatient($validated);

            // 2. Create Walk-In Appointment (with current time)
            $appointment = $this->createWalkInAppointment($patientId, $validated);

            // 3. Auto Check-in
            $appointment->checkInPatient(auth()->id());

            // 4. ✅ Assign Priority (Service handles urgency internally)
            $this->queueService->assignPriority(
                $appointment->appointment_id,
                $appointment->arrived_at
            );

            DB::commit();

            return redirect()
                ->route('receptionist.queue-ticket', $appointment->appointment_id)
                ->with('success', $this->getSuccessMessage($validated['urgency_level']));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    private function getOrCreatePatient(array $validated)
    {
        if (!empty($validated['patient_id'])) {
            return $validated['patient_id'];
        }

        $icNumber = $validated['new_patient_ic'];
        $cleanIC = str_replace(['-', ' '], '', $icNumber);
        
        if (!empty($validated['new_patient_email'])) {
            $email = $validated['new_patient_email'];
            $accountCompleted = false;
            $token = Str::random(64);
        } else {
            $email = $cleanIC . '@medilink.local';
            $accountCompleted = true;
            $token = null;
        }

        $user = User::create([
            'name' => $validated['new_patient_name'],
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
            'role' => 'patient',
            'account_completed' => $accountCompleted,
            'registered_by_staff' => true,
            'account_completion_token' => $token,
        ]);

        $dobAndGender = $this->extractFromIC($cleanIC);

        $patient = Patient::create([
            'user_id' => $user->id,
            'ic_number' => $icNumber,
            'phone_number' => PhoneHelper::standardize($validated['new_patient_phone']),
            'gender' => $dobAndGender['gender'],
            'date_of_birth' => $dobAndGender['dob'],
            'emergency_contact' => null,
        ]);

        return $patient->patient_id;
    }

    private function extractFromIC($ic)
    {
        $data = ['dob' => '2000-01-01', 'gender' => 'Other'];

        if (strlen($ic) == 12 && is_numeric($ic)) {
            $year = substr($ic, 0, 2);
            $month = substr($ic, 2, 2);
            $day = substr($ic, 4, 2);
            
            $currentYear = date('y');
            $century = ($year > $currentYear) ? '19' : '20';
            $data['dob'] = "$century$year-$month-$day";

            $lastDigit = substr($ic, -1);
            $data['gender'] = ($lastDigit % 2 != 0) ? 'Male' : 'Female';
        }

        return $data;
    }

    /**
     * ✅ Create walk-in with current time (service handles priority)
     */
    private function createWalkInAppointment($patientId, array $validated)
    {
        return Appointment::create([
            'patient_id' => $patientId,
            'doctor_id' => $validated['doctor_id'],
            'appointment_date' => today(),
            'appointment_time' => now(), // Current time = when they arrived
            'status' => Appointment::STATUS_CONFIRMED,
            'reason' => $validated['reason'],
            'is_walk_in' => true,
            'urgency_level' => $validated['urgency_level'],
            'walk_in_notes' => $validated['walk_in_notes'],
        ]);
    }

    /**
     * ✅ Get context-aware success message
     */
    private function getSuccessMessage($urgencyLevel)
    {
        return match($urgencyLevel) {
            'emergency' => '🔴 EMERGENCY patient registered! Patient will be seen IMMEDIATELY.',
            'urgent' => '🟡 URGENT patient registered! Patient will be prioritized.',
            'routine' => '🟢 Walk-in patient registered successfully! Queue ticket generated.',
            default => 'Walk-in patient registered successfully!',
        };
    }
}