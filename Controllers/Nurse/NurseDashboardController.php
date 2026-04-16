<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\StaffAlert;
use App\Models\PatientNurseAssignment; // ✅ ADD THIS
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class NurseDashboardController extends Controller
{
    public function index()
    {
        $nurse = Auth::user()->nurse;

        // ========================================
        // STATISTICS
        // ========================================

        $todayAppointments = Appointment::whereDate('appointment_date', Carbon::today())
            ->whereIn('status', [
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
                Appointment::STATUS_VITALS_RECORDED,
                Appointment::STATUS_READY_FOR_DOCTOR,
                Appointment::STATUS_IN_CONSULTATION,
            ])
            ->count();

        // ✅ ADD: My assigned patients waiting
        $myAssignedWaiting = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('assigned_nurse_id', $nurse->nurse_id)
            ->whereIn('status', [
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
            ])
            ->count();

        $waitingForNurse = Appointment::whereDate('appointment_date', Carbon::today())
            ->whereIn('status', [
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
            ])
            ->count();

        $readyForDoctor = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('status', Appointment::STATUS_READY_FOR_DOCTOR)
            ->count();

        $withDoctor = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('status', Appointment::STATUS_IN_CONSULTATION)
            ->count();

        $completedToday = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('status', Appointment::STATUS_COMPLETED)
            ->count();

        $urgentTasks = 0;

        $criticalAlerts = StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'nurse')
            ->where('is_read', false)
            ->where('priority', 'Critical')
            ->count();

        // ✅ ADD: Pending assignments count
        $pendingAssignments = PatientNurseAssignment::with([
            'appointment.patient.user',
            'appointment.doctor.user'
        ])
            ->where('nurse_id', $nurse->nurse_id)
            ->where('status', 'accepted')
            ->whereHas('appointment', function($query) {
                $query->whereDate('appointment_date', today())
                    ->whereIn('status', [
                        Appointment::STATUS_CHECKED_IN,
                        Appointment::STATUS_VITALS_PENDING,
                    ]);
            })
            ->orderBy('assigned_at', 'asc')
            ->limit(3)
            ->get();

        // ========================================
        // UPCOMING APPOINTMENTS
        // ========================================
        $upcomingAppointments = Appointment::with(['patient.user', 'doctor.user'])
            ->whereDate('appointment_date', Carbon::today())
            ->whereIn('status', [
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_VITALS_PENDING,
                Appointment::STATUS_VITALS_RECORDED,
                Appointment::STATUS_READY_FOR_DOCTOR,
                Appointment::STATUS_IN_CONSULTATION,
            ])
            ->orderBy('appointment_time', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($appointment) {
                $appointment->needs_vitals = in_array($appointment->status, [
                    Appointment::STATUS_CHECKED_IN,
                    Appointment::STATUS_VITALS_PENDING,
                ]);
                $appointment->ready_for_doctor = $appointment->status === Appointment::STATUS_READY_FOR_DOCTOR;
                $appointment->with_doctor = $appointment->status === Appointment::STATUS_IN_CONSULTATION;
                $appointment->awaiting_checkin = $appointment->status === Appointment::STATUS_CONFIRMED;
                return $appointment;
            });

        // ========================================
        // ACTIVE PATIENTS
        // ========================================
        $activePatients = Patient::with(['user', 'appointments' => function ($query) {
            $query->whereDate('appointment_date', Carbon::today())
                ->whereIn('status', [
                    Appointment::STATUS_CHECKED_IN,
                    Appointment::STATUS_VITALS_PENDING,
                    Appointment::STATUS_VITALS_RECORDED,
                    Appointment::STATUS_READY_FOR_DOCTOR,
                    Appointment::STATUS_IN_CONSULTATION,
                ]);
        }])
            ->whereHas('appointments', function ($query) {
                $query->whereDate('appointment_date', Carbon::today())
                    ->whereIn('status', [
                        Appointment::STATUS_CHECKED_IN,
                        Appointment::STATUS_VITALS_PENDING,
                        Appointment::STATUS_VITALS_RECORDED,
                        Appointment::STATUS_READY_FOR_DOCTOR,
                        Appointment::STATUS_IN_CONSULTATION,
                    ]);
            })
            ->limit(6)
            ->get();

        // ========================================
        // RECENT ALERTS
        // ========================================
        $recentAlerts = StaffAlert::with(['patient.user'])
            ->where('recipient_id', auth()->id())
            ->where('recipient_type', 'nurse')
            ->where('is_read', false)
            ->whereIn('priority', ['High', 'Urgent', 'Critical'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // ========================================
        // PASS DATA TO VIEW
        // ========================================
        $stats = [
            'today_appointments' => $todayAppointments,
            'my_assigned_waiting' => $myAssignedWaiting, // ✅ NEW
            'waiting_for_nurse' => $waitingForNurse,
            'ready_for_doctor' => $readyForDoctor,
            'with_doctor' => $withDoctor,
            'completed_today' => $completedToday,
            'urgent_tasks' => $urgentTasks,
            'critical_alerts' => $criticalAlerts,
        ];

        return view('nurse.nurse_dashboard', compact(
            'nurse',
            'stats',
            'upcomingAppointments',
            'activePatients',
            'recentAlerts',
            'pendingAssignments' // ✅ NEW
        ));
    }
}