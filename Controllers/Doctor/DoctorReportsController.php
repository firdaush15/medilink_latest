<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\DoctorRating;
use App\Models\MentalHealthAssessment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DoctorReportsController extends Controller
{
    public function index(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        if (!$doctor) {
            return redirect()->route('doctor.dashboard')->with('error', 'Doctor profile not found');
        }

        $timeFilter = $request->get('time_filter', 'month');

        // Calculate date ranges
        [$startDate, $endDate, $previousStartDate, $previousEndDate] = $this->getDateRanges($timeFilter);

        // ============ PERFORMANCE METRICS ============

        // Current period stats
        $currentStats = $this->getStatistics($doctor->doctor_id, $startDate, $endDate);

        // Previous period stats (for comparison)
        $previousStats = $this->getStatistics($doctor->doctor_id, $previousStartDate, $previousEndDate);

        // Calculate percentage changes
        $changes = $this->calculateChanges($currentStats, $previousStats);

        // ============ MENTAL HEALTH METRICS ============

        // Total mental health assessments for your patients in this period
        $mentalHealthAssessments = MentalHealthAssessment::whereHas('patient.appointments', function ($q) use ($doctor, $startDate, $endDate) {
            $q->where('doctor_id', $doctor->doctor_id)
                ->whereBetween('appointment_date', [$startDate, $endDate]);
        })->count();

        // Critical mental health cases (severe/moderate risk in last 3 months)
        $criticalMentalHealth = MentalHealthAssessment::whereHas('patient.appointments', function ($q) use ($doctor) {
            $q->where('doctor_id', $doctor->doctor_id);
        })
            ->whereIn('risk_level', ['severe', 'moderate'])
            ->where('assessment_date', '>=', now()->subMonths(3))
            ->count();

        // ============ PATIENT INSIGHTS ============

        // New vs Returning Patients
        $newPatients = Patient::whereHas('appointments', function ($q) use ($doctor, $startDate, $endDate) {
            $q->where('doctor_id', $doctor->doctor_id)
                ->whereBetween('appointment_date', [$startDate, $endDate]);
        })
            ->whereHas('appointments', function ($q) use ($doctor) {
                $q->where('doctor_id', $doctor->doctor_id);
            }, '=', 1)
            ->count();

        $returningPatients = Patient::whereHas('appointments', function ($q) use ($doctor, $startDate, $endDate) {
            $q->where('doctor_id', $doctor->doctor_id)
                ->whereBetween('appointment_date', [$startDate, $endDate]);
        })
            ->whereHas('appointments', function ($q) use ($doctor) {
                $q->where('doctor_id', $doctor->doctor_id);
            }, '>', 1)
            ->count();

        // ============ RATING & SATISFACTION ============

        // Average rating
        $averageRating = DoctorRating::where('doctor_id', $doctor->doctor_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->avg('rating') ?? 0;

        // Rating distribution
        $ratingDistribution = DoctorRating::where('doctor_id', $doctor->doctor_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('rating', DB::raw('COUNT(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get();

        // Total ratings received
        $totalRatings = DoctorRating::where('doctor_id', $doctor->doctor_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // ============ APPOINTMENT ANALYTICS ============

        // Cancellation rate
        $totalAppointments = $currentStats['total_appointments'];
        $cancelledAppointments = $currentStats['cancelled_appointments'];
        $cancellationRate = $totalAppointments > 0 ? round(($cancelledAppointments / $totalAppointments) * 100, 1) : 0;

        // Average appointments per day
        $daysInPeriod = $startDate->diffInDays($endDate) + 1;
        $avgAppointmentsPerDay = $totalAppointments > 0 ? round($totalAppointments / $daysInPeriod, 1) : 0;

        // ============ PRODUCTIVITY METRICS ============

        // Documentation rate (records per appointment)
        $completedAppointments = $currentStats['completed_appointments'];
        $medicalRecordsCount = $currentStats['medical_records'];
        $documentationRate = $completedAppointments > 0 ? round(($medicalRecordsCount / $completedAppointments) * 100, 1) : 0;

        // Prescription rate (prescriptions per appointment)
        $prescriptionsCount = $currentStats['prescriptions'];
        $prescriptionRate = $completedAppointments > 0 ? round(($prescriptionsCount / $completedAppointments) * 100, 1) : 0;

        // ============ CHARTS DATA ============

        // 1. Appointment Trend (Last 30 days)
        $appointmentTrend = $this->getAppointmentTrend($doctor->doctor_id, 30);

        // 2. Status Distribution (Pie Chart)
        $statusDistribution = [
            ['status' => 'Confirmed', 'count' => $currentStats['confirmed_appointments'], 'color' => '#4f46e5'],
            ['status' => 'Completed', 'count' => $currentStats['completed_appointments'], 'color' => '#16a34a'],
            ['status' => 'Cancelled', 'count' => $currentStats['cancelled_appointments'], 'color' => '#ef4444'],
        ];

        // 3. Top Diagnoses/Record Types
        $topDiagnoses = MedicalRecord::where('doctor_id', $doctor->doctor_id)
            ->whereBetween('record_date', [$startDate, $endDate])
            ->select('record_type', DB::raw('COUNT(*) as count'))
            ->groupBy('record_type')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // 4. Top Medications
        $topMedications = DB::table('prescription_items')
            ->join('prescriptions', 'prescription_items.prescription_id', '=', 'prescriptions.prescription_id')
            ->where('prescriptions.doctor_id', $doctor->doctor_id)
            ->whereBetween('prescriptions.prescribed_date', [$startDate, $endDate])
            ->select('prescription_items.medicine_name', DB::raw('COUNT(*) as count'))
            ->groupBy('prescription_items.medicine_name')
            ->orderBy('count', 'desc')
            ->limit(7)
            ->get();

        // 5. Hourly Activity (Which hours are busiest)
        $hourlyActivity = Appointment::where('doctor_id', $doctor->doctor_id)
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->select(DB::raw('HOUR(appointment_time) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // 6. Patient Age Distribution - Get unique patients
        $ageDistribution = DB::table('patients')
            ->join('appointments', 'patients.patient_id', '=', 'appointments.patient_id')
            ->where('appointments.doctor_id', $doctor->doctor_id)
            ->whereBetween('appointments.appointment_date', [$startDate, $endDate])
            ->select(
                DB::raw('CASE 
                    WHEN TIMESTAMPDIFF(YEAR, patients.date_of_birth, CURDATE()) < 18 THEN "0-17"
                    WHEN TIMESTAMPDIFF(YEAR, patients.date_of_birth, CURDATE()) BETWEEN 18 AND 30 THEN "18-30"
                    WHEN TIMESTAMPDIFF(YEAR, patients.date_of_birth, CURDATE()) BETWEEN 31 AND 45 THEN "31-45"
                    WHEN TIMESTAMPDIFF(YEAR, patients.date_of_birth, CURDATE()) BETWEEN 46 AND 60 THEN "46-60"
                    ELSE "60+"
                END as age_group'),
                DB::raw('COUNT(DISTINCT patients.patient_id) as count')
            )
            ->groupBy('age_group')
            ->orderByRaw("FIELD(age_group, '0-17', '18-30', '31-45', '46-60', '60+')")
            ->get();

        // 7. Gender Distribution - Get unique patients
        $genderDistribution = DB::table('patients')
            ->join('appointments', 'patients.patient_id', '=', 'appointments.patient_id')
            ->where('appointments.doctor_id', $doctor->doctor_id)
            ->whereBetween('appointments.appointment_date', [$startDate, $endDate])
            ->select('patients.gender', DB::raw('COUNT(DISTINCT patients.patient_id) as count'))
            ->groupBy('patients.gender')
            ->get();

        // ============ TOP PERFORMERS ============

        // Most engaged patients (highest completion rate)
        $topEngagedPatients = Patient::whereHas('appointments', function ($q) use ($doctor) {
            $q->where('doctor_id', $doctor->doctor_id);
        })
            ->with('user')
            ->withCount([
                'appointments as total_appointments' => function ($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->doctor_id);
                },
                'appointments as completed_appointments' => function ($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->doctor_id)->where('status', 'completed');
                }
            ])
            ->get()
            ->filter(function ($patient) {
                return $patient->total_appointments >= 3; // At least 3 appointments
            })
            ->map(function ($patient) {
                $patient->completion_rate = $patient->total_appointments > 0
                    ? round(($patient->completed_appointments / $patient->total_appointments) * 100)
                    : 0;
                return $patient;
            })
            ->sortByDesc('completion_rate')
            ->take(5);

        // ============ RECENT ACTIVITY ============

        $recentRatings = DoctorRating::where('doctor_id', $doctor->doctor_id)
            ->with(['patient.user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Top diseases diagnosed in current period
        $diseaseTrend = DB::table('patient_diagnoses')
            ->join('diagnosis_codes', 'patient_diagnoses.diagnosis_code_id', '=', 'diagnosis_codes.diagnosis_code_id')
            ->where('patient_diagnoses.doctor_id', $doctor->doctor_id)
            ->whereBetween('patient_diagnoses.diagnosis_date', [$startDate, $endDate])
            ->where('patient_diagnoses.diagnosis_type', 'Primary') // Only count primary diagnoses
            ->select(
                'diagnosis_codes.diagnosis_code_id',
                'diagnosis_codes.diagnosis_name',
                'diagnosis_codes.icd10_code',
                'diagnosis_codes.category',
                'diagnosis_codes.is_infectious',
                DB::raw('COUNT(*) as total_cases')
            )
            ->groupBy(
                'diagnosis_codes.diagnosis_code_id',
                'diagnosis_codes.diagnosis_name',
                'diagnosis_codes.icd10_code',
                'diagnosis_codes.category',
                'diagnosis_codes.is_infectious'
            )
            ->orderBy('total_cases', 'desc')
            ->limit(10)
            ->get();

        // Calculate trend (compare with previous period)
        $diseaseTrend = $diseaseTrend->map(function ($disease) use ($doctor, $startDate, $endDate, $previousStartDate, $previousEndDate) {
            $currentCount = $disease->total_cases;

            $previousCount = DB::table('patient_diagnoses')
                ->where('doctor_id', $doctor->doctor_id)
                ->where('diagnosis_code_id', $disease->diagnosis_code_id)
                ->whereBetween('diagnosis_date', [$previousStartDate, $previousEndDate])
                ->count();

            $disease->previous_cases = $previousCount;
            $disease->trend_change = $previousCount > 0
                ? round((($currentCount - $previousCount) / $previousCount) * 100)
                : ($currentCount > 0 ? 100 : 0);

            return $disease;
        });

        // Timeline data for disease trend chart (last 30 days)
        $diseaseTrendTimeline = $this->getDiseaseTrendTimeline($doctor->doctor_id, 30);

        // Infectious diseases count
        $infectiousDiseasesCount = DB::table('patient_diagnoses')
            ->join('diagnosis_codes', 'patient_diagnoses.diagnosis_code_id', '=', 'diagnosis_codes.diagnosis_code_id')
            ->where('patient_diagnoses.doctor_id', $doctor->doctor_id)
            ->where('diagnosis_codes.is_infectious', true)
            ->whereBetween('patient_diagnoses.diagnosis_date', [$startDate, $endDate])
            ->count();

        // Top infectious diseases
        $topInfectiousDiseases = DB::table('patient_diagnoses')
            ->join('diagnosis_codes', 'patient_diagnoses.diagnosis_code_id', '=', 'diagnosis_codes.diagnosis_code_id')
            ->where('patient_diagnoses.doctor_id', $doctor->doctor_id)
            ->where('diagnosis_codes.is_infectious', true)
            ->whereBetween('patient_diagnoses.diagnosis_date', [$startDate, $endDate])
            ->select(
                'diagnosis_codes.diagnosis_name',
                'diagnosis_codes.category',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('diagnosis_codes.diagnosis_name', 'diagnosis_codes.category')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Seasonal pattern (monthly breakdown for current year)
        $seasonalPattern = DB::table('patient_diagnoses')
            ->where('doctor_id', $doctor->doctor_id)
            ->whereYear('diagnosis_date', now()->year)
            ->select(
                DB::raw('MONTH(diagnosis_date) as month'),
                DB::raw('MONTHNAME(diagnosis_date) as month_name'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('month', 'month_name')
            ->orderBy('month')
            ->get();

        // ADD THESE VARIABLES TO THE compact() CALL:
        return view('doctor.doctor_reports', compact(
            'doctor',
            'timeFilter',
            'currentStats',
            'changes',
            'mentalHealthAssessments',
            'criticalMentalHealth',
            'newPatients',
            'returningPatients',
            'averageRating',
            'ratingDistribution',
            'totalRatings',
            'cancellationRate',
            'avgAppointmentsPerDay',
            'documentationRate',
            'prescriptionRate',
            'appointmentTrend',
            'statusDistribution',
            'topDiagnoses',
            'topMedications',
            'hourlyActivity',
            'ageDistribution',
            'genderDistribution',
            'topEngagedPatients',
            'recentRatings',

            // ✅ NEW: Disease trend variables
            'diseaseTrend',
            'diseaseTrendTimeline',
            'infectiousDiseasesCount',
            'topInfectiousDiseases',
            'seasonalPattern'
        ));
    }

    private function getDateRanges($timeFilter)
    {
        switch ($timeFilter) {
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                $previousStartDate = Carbon::now()->subWeek()->startOfWeek();
                $previousEndDate = Carbon::now()->subWeek()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                $previousStartDate = Carbon::now()->subMonth()->startOfMonth();
                $previousEndDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                $previousStartDate = Carbon::now()->subYear()->startOfYear();
                $previousEndDate = Carbon::now()->subYear()->endOfYear();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                $previousStartDate = Carbon::now()->subMonth()->startOfMonth();
                $previousEndDate = Carbon::now()->subMonth()->endOfMonth();
        }

        return [$startDate, $endDate, $previousStartDate, $previousEndDate];
    }

    private function getStatistics($doctorId, $startDate, $endDate)
    {
        return [
            'total_appointments' => Appointment::where('doctor_id', $doctorId)
                ->whereBetween('appointment_date', [$startDate, $endDate])
                ->count(),
            'confirmed_appointments' => Appointment::where('doctor_id', $doctorId)
                ->where('status', 'confirmed')
                ->whereBetween('appointment_date', [$startDate, $endDate])
                ->count(),
            'completed_appointments' => Appointment::where('doctor_id', $doctorId)
                ->where('status', 'completed')
                ->whereBetween('appointment_date', [$startDate, $endDate])
                ->count(),
            'cancelled_appointments' => Appointment::where('doctor_id', $doctorId)
                ->where('status', 'cancelled')
                ->whereBetween('appointment_date', [$startDate, $endDate])
                ->count(),
            'medical_records' => MedicalRecord::where('doctor_id', $doctorId)
                ->whereBetween('record_date', [$startDate, $endDate])
                ->count(),
            'prescriptions' => Prescription::where('doctor_id', $doctorId)
                ->whereBetween('prescribed_date', [$startDate, $endDate])
                ->count(),
        ];
    }

    private function calculateChanges($current, $previous)
    {
        $changes = [];
        foreach ($current as $key => $value) {
            if ($previous[$key] > 0) {
                $change = (($value - $previous[$key]) / $previous[$key]) * 100;
                $changes[$key] = round($change, 1);
            } else {
                $changes[$key] = $value > 0 ? 100 : 0;
            }
        }
        return $changes;
    }

    private function getAppointmentTrend($doctorId, $days)
    {
        $trend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $date)
                ->count();

            $trend[] = [
                'date' => $date->format('M d'),
                'count' => $count
            ];
        }
        return $trend;
    }

    /**
     * Get disease trend timeline for chart
     */
    private function getDiseaseTrendTimeline($doctorId, $days)
    {
        // Get top 5 diseases in this period
        $topDiseases = DB::table('patient_diagnoses')
            ->join('diagnosis_codes', 'patient_diagnoses.diagnosis_code_id', '=', 'diagnosis_codes.diagnosis_code_id')
            ->where('patient_diagnoses.doctor_id', $doctorId)
            ->where('patient_diagnoses.diagnosis_date', '>=', Carbon::now()->subDays($days))
            ->select(
                'diagnosis_codes.diagnosis_code_id',
                'diagnosis_codes.diagnosis_name',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('diagnosis_codes.diagnosis_code_id', 'diagnosis_codes.diagnosis_name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Generate labels (dates)
        $labels = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $labels[] = Carbon::now()->subDays($i)->format('M d');
        }

        // Color palette
        $colors = ['#4f46e5', '#ef4444', '#f59e0b', '#10b981', '#8b5cf6'];

        // Generate datasets for each disease
        $datasets = [];
        foreach ($topDiseases as $index => $disease) {
            $data = [];

            // Count cases for each day
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');

                $count = DB::table('patient_diagnoses')
                    ->where('doctor_id', $doctorId)
                    ->where('diagnosis_code_id', $disease->diagnosis_code_id)
                    ->whereDate('diagnosis_date', $date)
                    ->count();

                $data[] = $count;
            }

            $datasets[] = [
                'name' => $disease->diagnosis_name,
                'data' => $data,
                'color' => $colors[$index % count($colors)]
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets
        ];
    }
}
