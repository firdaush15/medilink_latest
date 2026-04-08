<?php
// app/Http/Controllers/Admin/AdminDashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Conversation;
use App\Models\Doctor;
use App\Models\LeaveRequest;
use App\Models\MedicineInventory;
use App\Models\Patient;
use App\Models\RestockRequest;
use App\Models\StaffAlert;
use App\Models\PrescriptionDispensing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // ── KPI: Doctors ──────────────────────────────────────────────
        $totalDoctors    = Doctor::count();
        $newDoctors      = Doctor::where('created_at', '>=', now()->subWeek())->count();
        $activeDoctors   = Doctor::where('availability_status', 'Available')->count();

        // ── KPI: Patients ─────────────────────────────────────────────
        $totalPatients = Patient::count();
        $newPatients   = Patient::where('created_at', '>=', now()->subWeek())->count();

        // ── KPI: Appointments (today) ─────────────────────────────────
        $todayAppointments = Appointment::whereDate('appointment_date', today())->count();

        $completedToday = Appointment::whereDate('appointment_date', today())
            ->where('status', 'completed')
            ->count();

        $inProgressToday = Appointment::whereDate('appointment_date', today())
            ->whereIn('status', ['checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
            ->count();

        // ── KPI: Cancellations ────────────────────────────────────────
        $cancelledAppointments = Appointment::where('status', 'cancelled')->count();
        $cancelledThisWeek     = Appointment::where('status', 'cancelled')
            ->where('updated_at', '>=', now()->subWeek())
            ->count();

        // ── Quick-action counters ─────────────────────────────────────
        $pendingLeaveCount    = LeaveRequest::where('status', 'pending')->count();
        $pendingRestockCount  = RestockRequest::where('status', 'Pending')->count();
        $lowStockCount        = MedicineInventory::lowStock()->count();
        $outOfStockCount      = MedicineInventory::outOfStock()->count();
        $pendingRxCount       = PrescriptionDispensing::where('verification_status', 'Pending')->count();

        // ── Today's appointment status breakdown ──────────────────────
        $todayStatusBreakdown = Appointment::whereDate('appointment_date', today())
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();

        // ── Top 5 doctors by appointments this week ───────────────────
        $topDoctorsThisWeek = DB::table('appointments')
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.doctor_id')
            ->join('users',   'doctors.user_id',        '=', 'users.id')
            ->whereBetween('appointments.appointment_date', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->select(
                'users.name',
                'doctors.specialization',
                DB::raw('count(*) as total'),
                DB::raw("sum(case when appointments.status = 'completed' then 1 else 0 end) as completed")
            )
            ->groupBy('doctors.doctor_id', 'users.name', 'doctors.specialization')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ── Upcoming confirmed appointments (paginated) ───────────────
        $upcomingAppointments = Appointment::whereDate('appointment_date', '>=', today())
            ->whereIn('status', ['confirmed', 'checked_in', 'vitals_pending',
                                 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
            ->with(['doctor.user', 'patient.user'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(8);

        // ── Pending leave requests (latest 5) ────────────────────────
        $pendingLeaves = LeaveRequest::where('status', 'pending')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ── Recent staff alerts sent to admin ─────────────────────────
        $recentAlerts = StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'admin')
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // ── Recent admin conversations (messages) ─────────────────────
        $recentConversations = Conversation::where('admin_id', auth()->id())
            ->where('status', '!=', 'archived')
            ->with(['latestMessage', 'doctor.user'])
            ->orderByDesc('last_message_at')
            ->limit(5)
            ->get();

        // ── Low-stock medicines (latest 5) ────────────────────────────
        $lowStockMedicines = MedicineInventory::lowStock()
            ->orderBy('quantity_in_stock')
            ->limit(5)
            ->get();

        return view('admin.admin_dashboard', compact(
            // KPIs
            'totalDoctors',
            'newDoctors',
            'activeDoctors',
            'totalPatients',
            'newPatients',
            'todayAppointments',
            'completedToday',
            'inProgressToday',
            'cancelledAppointments',
            'cancelledThisWeek',

            // Quick-action counters
            'pendingLeaveCount',
            'pendingRestockCount',
            'lowStockCount',
            'outOfStockCount',
            'pendingRxCount',

            // Charts / breakdowns
            'todayStatusBreakdown',
            'topDoctorsThisWeek',

            // Tables
            'upcomingAppointments',
            'pendingLeaves',
            'recentAlerts',
            'recentConversations',
            'lowStockMedicines',
        ));
    }
}