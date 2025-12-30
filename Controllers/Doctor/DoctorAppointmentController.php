<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;

class DoctorAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        if (!$doctor) {
            return redirect()->route('dashboard')->with('error', 'Doctor profile not found');
        }

        $timeFilter = $request->get('time_filter', 'today');
        $statusFilter = $request->get('status_filter', 'ready'); // ✅ Changed default to 'ready'

        // ✅ NEW: Get only appointments that are READY FOR DOCTOR or IN CONSULTATION for the cards section
        $filteredAppointmentsQuery = Appointment::where('doctor_id', $doctor->doctor_id)
            ->whereIn('status', [
                Appointment::STATUS_READY_FOR_DOCTOR,
                Appointment::STATUS_IN_CONSULTATION
            ]);

        switch ($timeFilter) {
            case 'today':
                $filteredAppointmentsQuery->whereDate('appointment_date', Carbon::today());
                break;
            case 'week':
                $filteredAppointmentsQuery->whereBetween('appointment_date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'month':
                $filteredAppointmentsQuery->whereMonth('appointment_date', Carbon::now()->month)
                    ->whereYear('appointment_date', Carbon::now()->year);
                break;
            case 'year':
                $filteredAppointmentsQuery->whereYear('appointment_date', Carbon::now()->year);
                break;
        }

        $filteredAppointments = $filteredAppointmentsQuery
            ->with(['patient.user', 'latestVital', 'receptionistWhoCheckedIn', 'nurseWhoRecordedVitals.user'])
            ->orderBy('queue_number', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->get();

        // Build query for table based on time filter and status filter
        $query = Appointment::where('doctor_id', $doctor->doctor_id);

        switch ($timeFilter) {
            case 'today':
                $query->whereDate('appointment_date', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('appointment_date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereMonth('appointment_date', Carbon::now()->month)
                    ->whereYear('appointment_date', Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear('appointment_date', Carbon::now()->year);
                break;
        }

        // ✅ UPDATED: Apply status filter with proper workflow statuses
        switch ($statusFilter) {
            case 'confirmed':
                $query->where('status', Appointment::STATUS_CONFIRMED);
                break;
            case 'ready':
                $query->where('status', Appointment::STATUS_READY_FOR_DOCTOR);
                break;
            case 'in_consultation':
                $query->where('status', Appointment::STATUS_IN_CONSULTATION);
                break;
            case 'completed':
                $query->where('status', Appointment::STATUS_COMPLETED);
                break;
            case 'cancelled':
                $query->where('status', Appointment::STATUS_CANCELLED);
                break;
        }

        $appointments = $query->with(['patient.user'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->get();

        // ✅ UPDATED: Calculate statistics with proper workflow statuses
        $stats = [
            'total' => Appointment::where('doctor_id', $doctor->doctor_id)->count(),
            'ready_for_doctor' => Appointment::where('doctor_id', $doctor->doctor_id)
                ->where('status', Appointment::STATUS_READY_FOR_DOCTOR)
                ->whereDate('appointment_date', '>=', Carbon::today())
                ->count(),
            'in_consultation' => Appointment::where('doctor_id', $doctor->doctor_id)
                ->where('status', Appointment::STATUS_IN_CONSULTATION)
                ->whereDate('appointment_date', Carbon::today())
                ->count(),
            'completed' => Appointment::where('doctor_id', $doctor->doctor_id)
                ->where('status', Appointment::STATUS_COMPLETED)
                ->count(),
            'cancelled' => Appointment::where('doctor_id', $doctor->doctor_id)
                ->where('status', Appointment::STATUS_CANCELLED)
                ->count(),
        ];

        return view('doctor.doctor_appointments', compact('filteredAppointments', 'appointments', 'stats', 'timeFilter', 'statusFilter'));
    }

    public function show($id)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        $appointment = Appointment::where('appointment_id', $id)
            ->where('doctor_id', $doctor->doctor_id)
            ->with(['patient.user'])
            ->firstOrFail();

        return response()->json([
            'patient_name' => $appointment->patient->user->name,
            'date' => Carbon::parse($appointment->appointment_date)->format('d/m/Y'),
            'time' => Carbon::parse($appointment->appointment_time)->format('g:i A'),
            'reason' => $appointment->reason,
            'status' => $appointment->getCurrentStageDisplay(),
            'patient_phone' => $appointment->patient->phone_number,
            'patient_gender' => $appointment->patient->gender,
            'workflow_stage' => $appointment->status,
            'can_start_consultation' => $appointment->isReadyForDoctor(),
        ]);
    }

    /**
     * ✅ NEW: Start consultation (doctor begins seeing patient)
     */
    public function startConsultation($id)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        $appointment = Appointment::where('appointment_id', $id)
            ->where('doctor_id', $doctor->doctor_id)
            ->firstOrFail();

        // ✅ VALIDATION: Check if patient is ready
        if (!$appointment->isReadyForDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Patient is not ready for consultation. Current status: ' . $appointment->getCurrentStageDisplay()
            ], 400);
        }

        try {
            $appointment->startConsultation($doctor->user_id);

            return response()->json([
                'success' => true,
                'message' => 'Consultation started successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ UPDATED: Complete appointment (with validation)
     */
    public function complete($id)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        $appointment = Appointment::where('appointment_id', $id)
            ->where('doctor_id', $doctor->doctor_id)
            ->firstOrFail();

        // ✅ VALIDATION: Check if consultation has started
        if (!$appointment->isWithDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot complete appointment. Consultation has not started yet. Current status: ' . $appointment->getCurrentStageDisplay()
            ], 400);
        }

        try {
            $appointment->completeConsultation($doctor->user_id);

            return response()->json([
                'success' => true,
                'message' => 'Appointment marked as completed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ UPDATED: Update patient page (with billing procedures)
     */
    public function updatePatientPage($id)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        $appointment = Appointment::where('appointment_id', $id)
            ->where('doctor_id', $doctor->doctor_id)
            ->with([
                'patient.user',
                'patient.medicalRecords',
                'patient.activeAllergies',
                'prescriptions.items',
                'latestVital',
                'billingItems.addedBy'  // ✅ ADDED THIS
            ])
            ->firstOrFail();

        // ✅ VALIDATION: Check if patient is ready or already in consultation
        if (!$appointment->isReadyForDoctor() && !$appointment->isWithDoctor() && !$appointment->isCompleted()) {
            return redirect()
                ->route('doctor.appointments')
                ->with('error', 'Cannot access patient details. Patient must complete check-in and vitals recording first. Current status: ' . $appointment->getCurrentStageDisplay());
        }

        // ✅ NEW: Get procedures grouped by category for the selector
        $proceduresByCategory = \App\Models\ProcedurePrice::active()
            ->orderBy('category')
            ->orderBy('procedure_name')
            ->get()
            ->groupBy('category');

        return view('doctor.update_patient', compact('appointment', 'doctor', 'proceduresByCategory'));
    }
}
