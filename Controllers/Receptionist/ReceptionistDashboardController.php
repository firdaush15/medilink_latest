<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReceptionistDashboardController extends Controller
{
    public function index()
    {
        // Get today's appointments with relationships
        $appointments = Appointment::with(['patient.user', 'doctor.user'])
            ->whereDate('appointment_date', today())
            ->whereIn('status', [
                'confirmed',
                'checked_in',
                'vitals_pending',
                'vitals_recorded',
                'ready_for_doctor',
                'in_consultation'
            ])
            ->orderBy('appointment_time', 'asc')
            ->get();

        // Calculate statistics
        $todayAppointments = $appointments->count();

        // ✅ FIX: Count only patients who have actually checked in
        // Status must be anything AFTER 'confirmed'
        $checkedInCount = $appointments->whereIn('status', [
            'checked_in',
            'vitals_pending',
            'vitals_recorded',
            'ready_for_doctor',
            'in_consultation'
        ])->count();

        // ✅ FIX: Waiting room = checked in but NOT yet with doctor
        $waitingCount = $appointments->whereIn('status', [
            'checked_in',
            'vitals_pending',
            'vitals_recorded',
            'ready_for_doctor'
        ])->count();

        // Total unique patients today
        $totalPatientsToday = $appointments->unique('patient_id')->count();

        // Get all doctors with their appointment counts for today
        $doctors = Doctor::with('user')
            ->whereHas('user', function ($query) {
                $query->where('role', 'doctor');
            })
            ->get();

        return view('receptionist.receptionist_dashboard', compact(
            'appointments',
            'todayAppointments',
            'checkedInCount',
            'waitingCount',
            'totalPatientsToday',
            'doctors'
        ));
    }
}