<?php
// app/Http/Controllers/Api/DoctorApiController.php
// Place at: app/Http/Controllers/Api/DoctorApiController.php
// Add routes in routes/api.php (see comments at bottom of file)

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DoctorApiController extends Controller
{
    // =============================================
    // GET DOCTOR DASHBOARD STATS
    // POST /api/doctor/dashboard
    // Body: { user_id }
    // =============================================
    public function dashboard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $doctor = Doctor::where('user_id', $request->user_id)->first();

            if (! $doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor record not found',
                ], 404);
            }

            $today = Carbon::today();

            // Today's appointments
            $todayAppointments = Appointment::with(['patient.user'])
                ->where('doctor_id', $doctor->doctor_id)
                ->whereDate('appointment_date', $today)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->orderBy('appointment_time')
                ->get();

            // Stats
            $totalPatients = Appointment::where('doctor_id', $doctor->doctor_id)
                ->distinct('patient_id')
                ->count('patient_id');

            $completedToday = Appointment::where('doctor_id', $doctor->doctor_id)
                ->whereDate('appointment_date', $today)
                ->where('status', 'completed')
                ->count();

            $pendingReports = Appointment::where('doctor_id', $doctor->doctor_id)
                ->whereDate('appointment_date', $today)
                ->whereIn('status', ['ready_for_doctor', 'checked_in', 'vitals_recorded'])
                ->count();

            return response()->json([
                'success' => true,
                'doctor'  => [
                    'doctor_id'      => $doctor->doctor_id,
                    'name'           => $doctor->user->name,
                    'specialization' => $doctor->specialization,
                    'availability'   => $doctor->availability_status,
                ],
                'stats' => [
                    'today_appointments' => $todayAppointments->count(),
                    'total_patients'     => $totalPatients,
                    'completed_today'    => $completedToday,
                    'pending_reports'    => $pendingReports,
                ],
                'today_appointments' => $todayAppointments->map(
                    fn ($a) => $this->formatAppointment($a)
                ),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // =============================================
    // GET DOCTOR APPOINTMENTS
    // POST /api/doctor/appointments
    // Body: { user_id, date? (YYYY-MM-DD), status? }
    // =============================================
    public function appointments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'date'    => 'nullable|date',
            'status'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $doctor = Doctor::where('user_id', $request->user_id)->first();

            if (! $doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor record not found',
                ], 404);
            }

            $query = Appointment::with(['patient.user'])
                ->where('doctor_id', $doctor->doctor_id)
                ->whereNotIn('status', ['cancelled', 'no_show']);

            if ($request->filled('date')) {
                $query->whereDate('appointment_date', $request->date);
            } else {
                // Default: today
                $query->whereDate('appointment_date', Carbon::today());
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $appointments = $query->orderBy('appointment_time')->get();

            return response()->json([
                'success'      => true,
                'appointments' => $appointments->map(
                    fn ($a) => $this->formatAppointment($a)
                ),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch appointments',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // =============================================
    // GET APPOINTMENT DETAIL
    // POST /api/doctor/appointments/detail
    // Body: { appointment_id }
    // =============================================
    public function appointmentDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|exists:appointments,appointment_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $appointment = Appointment::with([
                'patient.user',
                'patient.allergies',
            ])->find($request->appointment_id);

            return response()->json([
                'success'     => true,
                'appointment' => $this->formatAppointment($appointment, detailed: true),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch appointment detail',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // =============================================
    // START CONSULTATION
    // POST /api/doctor/appointments/start
    // Body: { appointment_id, user_id }
    // =============================================
    public function startConsultation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|exists:appointments,appointment_id',
            'user_id'        => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $appointment = Appointment::find($request->appointment_id);

            $appointment->update([
                'status'                  => 'in_consultation',
                'consultation_started_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Consultation started successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start consultation',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // =============================================
    // COMPLETE CONSULTATION
    // POST /api/doctor/appointments/complete
    // Body: { appointment_id, user_id }
    // =============================================
    public function completeConsultation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|exists:appointments,appointment_id',
            'user_id'        => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $appointment = Appointment::find($request->appointment_id);

            $appointment->update([
                'status'                 => 'completed',
                'consultation_ended_at'  => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Consultation completed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete consultation',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // =============================================
    // GET DOCTOR PATIENTS
    // POST /api/doctor/patients
    // Body: { user_id }
    // =============================================
    public function patients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $doctor = Doctor::where('user_id', $request->user_id)->first();

            if (! $doctor) {
                return response()->json([
                    'success'  => true,
                    'patients' => [],
                ]);
            }

            // Get distinct patients who had appointments with this doctor
            $patientIds = Appointment::where('doctor_id', $doctor->doctor_id)
                ->distinct('patient_id')
                ->pluck('patient_id');

            $patients = Patient::with(['user'])
                ->whereIn('patient_id', $patientIds)
                ->get()
                ->map(function ($patient) use ($doctor) {
                    $lastAppt = Appointment::where('doctor_id', $doctor->doctor_id)
                        ->where('patient_id', $patient->patient_id)
                        ->where('status', 'completed')
                        ->orderBy('appointment_date', 'desc')
                        ->first();

                    return [
                        'patient_id'  => $patient->patient_id,
                        'name'        => $patient->user->name,
                        'gender'      => $patient->gender,
                        'blood_type'  => $patient->blood_type ?? 'Unknown',
                        'age'         => $this->calculateAge($patient->date_of_birth),
                        'phone_number' => $patient->phone_number,
                        'last_visit'  => $lastAppt
                            ? Carbon::parse($lastAppt->appointment_date)->format('d M Y')
                            : null,
                    ];
                });

            return response()->json([
                'success'  => true,
                'patients' => $patients,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch patients',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Private helpers ───────────────────────────────────────
    private function formatAppointment(Appointment $a, bool $detailed = false): array
    {
        $base = [
            'appointment_id'   => $a->appointment_id,
            'patient_name'     => $a->patient->user->name ?? 'Unknown',
            'appointment_date' => Carbon::parse($a->appointment_date)->format('d M Y'),
            'appointment_time' => Carbon::parse($a->appointment_time)->format('h:i A'),
            'appointment_type' => $a->reason ?? 'Consultation',
            'status'           => $a->status,
            'reason'           => $a->reason,
        ];

        if ($detailed) {
            $base['patient_gender']     = $a->patient->gender ?? null;
            $base['patient_blood_type'] = $a->patient->blood_type ?? null;
            $base['patient_age']        = $this->calculateAge($a->patient->date_of_birth ?? null);
            $base['patient_phone']      = $a->patient->phone_number ?? null;
            $base['allergies']          = $a->patient->allergies
                ? $a->patient->allergies->map(fn($al) => [
                    'allergen_name' => $al->allergen_name,
                    'severity'      => $al->severity,
                  ])
                : [];
        }

        return $base;
    }

    private function calculateAge(?string $dob): ?int
    {
        if (! $dob) return null;
        try {
            return Carbon::parse($dob)->age;
        } catch (\Exception $e) {
            return null;
        }
    }
}

/*
|--------------------------------------------------------------------------
| ADD THESE ROUTES TO routes/api.php
|--------------------------------------------------------------------------
|
| use App\Http\Controllers\Api\DoctorApiController;
|
| // Doctor Mobile App Routes
| Route::prefix('doctor')->group(function () {
|     Route::post('/dashboard',            [DoctorApiController::class, 'dashboard']);
|     Route::post('/appointments',         [DoctorApiController::class, 'appointments']);
|     Route::post('/appointments/detail',  [DoctorApiController::class, 'appointmentDetail']);
|     Route::post('/appointments/start',   [DoctorApiController::class, 'startConsultation']);
|     Route::post('/appointments/complete',[DoctorApiController::class, 'completeConsultation']);
|     Route::post('/patients',             [DoctorApiController::class, 'patients']);
| });
|
*/