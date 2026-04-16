<?php
// app/Http/Controllers/Admin/AdminReportsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionDispensing;
use App\Models\MedicineInventory;
use App\Models\RestockRequest;
use App\Models\MedicineDisposal;
use App\Models\LeaveRequest;
use App\Models\DiagnosisCode;
use App\Models\PatientDiagnosis;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportsController extends Controller
{
    public function index(Request $request)
    {
        // ── Date range ────────────────────────────────────────────────
        $defaultFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $defaultTo   = Carbon::now()->format('Y-m-d');

        $from = $request->input('from', $defaultFrom);
        $to   = $request->input('to',   $defaultTo);

        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate   = Carbon::parse($to)->endOfDay();

        // ── KPI: Appointments ─────────────────────────────────────────
        $totalAppointments = Appointment::whereBetween('appointment_date', [$fromDate, $toDate])->count();

        $completedAppointments = Appointment::whereBetween('appointment_date', [$fromDate, $toDate])
            ->where('status', 'completed')
            ->count();

        $cancelledAppointments = Appointment::whereBetween('appointment_date', [$fromDate, $toDate])
            ->where('status', 'cancelled')
            ->count();

        // ── KPI: Patients ─────────────────────────────────────────────
        $totalPatients = Patient::count();
        $newPatients   = Patient::whereBetween('created_at', [$fromDate, $toDate])->count();

        // ── KPI: Doctors ──────────────────────────────────────────────
        $totalDoctors  = Doctor::count();
        $activeDoctors = Doctor::where('availability_status', 'Available')->count();

        // ── KPI: Prescriptions ────────────────────────────────────────
        $totalPrescriptions = Prescription::whereBetween('prescribed_date', [$fromDate, $toDate])->count();

        $dispensedPrescriptions = PrescriptionDispensing::whereBetween('dispensed_at', [$fromDate, $toDate])
            ->where('verification_status', 'Dispensed')
            ->count();

        $pendingPrescriptions = PrescriptionDispensing::where('verification_status', 'Pending')->count();

        $rejectedPrescriptions = PrescriptionDispensing::whereBetween('created_at', [$fromDate, $toDate])
            ->where('verification_status', 'Rejected')
            ->count();

        // ── KPI: Pharmacy stock ───────────────────────────────────────
        $lowStockCount   = MedicineInventory::lowStock()->count();
        $outOfStockCount = MedicineInventory::outOfStock()->count();

        // ── KPI: Leave ────────────────────────────────────────────────
        $pendingLeaves  = LeaveRequest::where('status', 'pending')->count();
        $approvedLeaves = LeaveRequest::where('status', 'approved')
            ->whereBetween('approved_at', [$fromDate, $toDate])
            ->count();

        // ── Appointment Status Breakdown ──────────────────────────────
        $appointmentStatusBreakdown = Appointment::whereBetween('appointment_date', [$fromDate, $toDate])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();

        // ── No-shows / late / walk-ins ────────────────────────────────
        $noShowCount = Appointment::whereBetween('appointment_date', [$fromDate, $toDate])
            ->where('status', 'no_show')
            ->count();

        $lateArrivalCount = Appointment::whereBetween('appointment_date', [$fromDate, $toDate])
            ->where('is_late', true)
            ->count();

        $flaggedPatients = Patient::where('is_flagged', true)->count();

        $walkInCount = Appointment::whereBetween('appointment_date', [$fromDate, $toDate])
            ->where('is_walk_in', true)
            ->count();

        // Average consultation duration (minutes)
        $avgConsultationMinutes = (int) (Appointment::whereBetween('appointment_date', [$fromDate, $toDate])
            ->whereNotNull('consultation_started_at')
            ->whereNotNull('consultation_ended_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, consultation_started_at, consultation_ended_at)) as avg_minutes')
            ->value('avg_minutes') ?? 0);

        // ── Top Doctors by Appointments ───────────────────────────────
        $topDoctors = DB::table('appointments')
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.doctor_id')
            ->join('users',   'doctors.user_id',        '=', 'users.id')
            ->whereBetween('appointments.appointment_date', [$fromDate, $toDate])
            ->select(
                'users.name',
                'doctors.specialization',
                DB::raw('count(*) as total'),
                DB::raw("sum(case when appointments.status = 'completed' then 1 else 0 end) as completed")
            )
            ->groupBy('doctors.doctor_id', 'users.name', 'doctors.specialization')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // ── Appointments by Specialisation ────────────────────────────
        $appointmentsBySpecialisation = DB::table('appointments')
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.doctor_id')
            ->whereBetween('appointments.appointment_date', [$fromDate, $toDate])
            ->select('doctors.specialization', DB::raw('count(*) as count'))
            ->groupBy('doctors.specialization')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        // ── Pharmacy Inventory by Category ────────────────────────────
        // Pre-aggregate expiring batches per medicine to avoid a correlated
        // subquery referencing a non-grouped column (MariaDB ONLY_FULL_GROUP_BY).
        $expiringSubquery = DB::table('medicine_batches')
            ->select('medicine_id', DB::raw('count(*) as expiring_count'))
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->whereRaw('expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)')
            ->whereRaw('expiry_date > CURDATE()')
            ->groupBy('medicine_id');

        $pharmacyByCategory = DB::table('medicine_inventory as m')
            ->leftJoinSub($expiringSubquery, 'eb', 'eb.medicine_id', '=', 'm.medicine_id')
            ->select(
                'm.category',
                DB::raw('count(*) as total'),
                DB::raw("sum(case when m.status = 'Low Stock' then 1 else 0 end) as low_stock"),
                DB::raw("sum(case when m.status = 'Out of Stock' then 1 else 0 end) as out_of_stock"),
                DB::raw('coalesce(sum(eb.expiring_count), 0) as expiring_soon')
            )
            ->groupBy('m.category')
            ->orderBy('m.category')
            ->get();

        // ── Restock Requests ──────────────────────────────────────────
        $restockPending  = RestockRequest::where('status', 'Pending')->count();
        $restockApproved = RestockRequest::where('status', 'Approved')->count();
        $restockOrdered  = RestockRequest::where('status', 'Ordered')->count();

        $restockReceived = RestockRequest::whereBetween('updated_at', [$fromDate, $toDate])
            ->where('status', 'Received')
            ->count();

        $restockRejected = RestockRequest::whereBetween('updated_at', [$fromDate, $toDate])
            ->where('status', 'Rejected')
            ->count();

        $totalDisposals = MedicineDisposal::whereBetween('disposed_at', [$fromDate, $toDate])->count();

        // ── Leave by Role ─────────────────────────────────────────────
        $leaveByRole = DB::table('leave_requests')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->select(
                'staff_role',
                DB::raw("sum(case when status = 'pending'  then 1 else 0 end) as pending"),
                DB::raw("sum(case when status = 'approved' then 1 else 0 end) as approved"),
                DB::raw("sum(case when status = 'rejected' then 1 else 0 end) as rejected")
            )
            ->groupBy('staff_role')
            ->orderBy('staff_role')
            ->get();

        // ── Patient Demographics ──────────────────────────────────────
        $patientsByGender = DB::table('patients')
            ->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->orderByDesc('count')
            ->get();

        $patientsByBloodType = DB::table('patients')
            ->select('blood_type', DB::raw('count(*) as count'))
            ->groupBy('blood_type')
            ->orderByDesc('count')
            ->get();

        // ── Top Diagnoses ─────────────────────────────────────────────
        $topDiagnoses = DB::table('patient_diagnoses as pd')
            ->join('diagnosis_codes as dc', 'pd.diagnosis_code_id', '=', 'dc.diagnosis_code_id')
            ->whereBetween('pd.diagnosis_date', [$fromDate, $toDate])
            ->select(
                'dc.icd10_code',
                'dc.diagnosis_name',
                DB::raw('count(*) as count')
            )
            ->groupBy('dc.diagnosis_code_id', 'dc.icd10_code', 'dc.diagnosis_name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // ── Pass to view ──────────────────────────────────────────────
        // ✅ Matches file location: resources/views/admin/admin_reports.blade.php
        return view('admin.admin_reports', compact(
            'defaultFrom',
            'defaultTo',
            'from',
            'to',

            // KPIs
            'totalAppointments',
            'completedAppointments',
            'cancelledAppointments',
            'totalPatients',
            'newPatients',
            'totalDoctors',
            'activeDoctors',
            'totalPrescriptions',
            'dispensedPrescriptions',
            'pendingPrescriptions',
            'rejectedPrescriptions',
            'lowStockCount',
            'outOfStockCount',
            'pendingLeaves',
            'approvedLeaves',

            // Appointment
            'appointmentStatusBreakdown',
            'noShowCount',
            'lateArrivalCount',
            'flaggedPatients',
            'walkInCount',
            'avgConsultationMinutes',

            // Doctors
            'topDoctors',
            'appointmentsBySpecialisation',

            // Pharmacy
            'pharmacyByCategory',
            'restockPending',
            'restockApproved',
            'restockOrdered',
            'restockReceived',
            'restockRejected',
            'totalDisposals',

            // Leave
            'leaveByRole',

            // Patients
            'patientsByGender',
            'patientsByBloodType',

            // Diagnoses
            'topDiagnoses',
        ));
    }
}