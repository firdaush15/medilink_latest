<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\VitalRecord;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NurseVitalsAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $nurse = auth()->user()->nurse;

        if (!$nurse) {
            abort(403, 'Nurse profile not found.');
        }

        // ✅ FIXED: Use get() before pluck()
        $assignedDoctorIds = $nurse->assignedDoctors()->get()->pluck('doctor_id');

        if ($assignedDoctorIds->isEmpty()) {
            return view('nurse.nurse_vitalSignsTrendsAnalytics', [
                'nurse' => $nurse,
                'patientsWithVitals' => collect(),
                'patient' => null,
                'vitalRecords' => collect(),
                'latestVital' => null,
                'vitalStats' => [],
                'aiInsights' => [],
                'selectedPatientId' => null,
                'timeRange' => '7d',
                'vitalType' => 'all',
                'chartData' => [],
                'criticalReadingsCount' => 0,
                'overviewStats' => [
                    'total_patients' => 0,
                    'critical_patients' => 0,
                    'recent_readings' => 0,
                ],
                'accessRestrictionMessage' => 'You are not currently assigned to any doctors. Please contact your supervisor.'
            ]);
        }

        // ✅ Tier 2: ACTIVE PATIENTS - Show patients with TODAY's appointments with assigned doctors
        // This is the patient list - only current patients for assigned doctors
        $patientsWithVitals = Patient::with('user')
            ->whereHas('appointments', function ($query) use ($assignedDoctorIds) {
                $query->whereIn('doctor_id', $assignedDoctorIds)
                    ->whereDate('appointment_date', '>=', Carbon::today()->subDays(7)); // Last 7 days appointments
            })
            ->whereHas('vitalRecords', function ($query) {
                $query->where('recorded_at', '>=', Carbon::now()->subDays(30));
            })
            ->orderBy('user_id')
            ->get();

        // Get patient selection
        $selectedPatientId = $request->get('patient_id');
        $timeRange = $request->get('time_range', '7d');
        $vitalType = $request->get('vital_type', 'all');

        // Initialize variables
        $patient = null;
        $vitalRecords = collect();
        $latestVital = null;
        $vitalStats = [];
        $aiInsights = [];
        $criticalReadingsCount = 0;

        if ($selectedPatientId) {
            // ✅ SMART ACCESS CONTROL: Verify nurse has access to this patient
            // Access granted if:
            // 1. Patient has appointment TODAY with nurse's assigned doctor, OR
            // 2. Patient has appointment in LAST 7 DAYS with nurse's assigned doctor (recent care)
            $hasAccess = Patient::where('patient_id', $selectedPatientId)
                ->whereHas('appointments', function ($query) use ($assignedDoctorIds) {
                    $query->whereIn('doctor_id', $assignedDoctorIds)
                        ->whereDate('appointment_date', '>=', Carbon::today()->subDays(7));
                })
                ->exists();

            if (!$hasAccess) {
                return redirect()->route('nurse.vitals-analytics')
                    ->with('error', '⛔ Access Denied: You can only view patients with recent appointments (last 7 days) with your assigned doctors.');
            }

            // ✅ CRITICAL: Once access is verified, nurse can see ALL vital history
            // This ensures continuity of care - nurse sees full patient history regardless of which doctor recorded it
            $patient = Patient::with([
                'user',
                'appointments' => function ($query) {
                    $query->whereDate('appointment_date', '>=', Carbon::today()->subDays(30))
                        ->with('doctor.user') // Load doctor info for ALL appointments
                        ->latest();
                }
            ])->findOrFail($selectedPatientId);

            // Get ALL vital records for this patient (not filtered by doctor)
            $startDate = $this->getStartDate($timeRange);

            // ✅ NO DOCTOR FILTERING - Nurse sees complete vital history
            $vitalRecords = VitalRecord::with('nurse.user') // Show who recorded each vital
                ->where('patient_id', $selectedPatientId)
                ->where('recorded_at', '>=', $startDate)
                ->orderBy('recorded_at', 'asc')
                ->get();

            $latestVital = VitalRecord::where('patient_id', $selectedPatientId)
                ->latest('recorded_at')
                ->first();

            // Count critical readings
            $criticalReadingsCount = VitalRecord::where('patient_id', $selectedPatientId)
                ->where('recorded_at', '>=', $startDate)
                ->where('is_critical', true)
                ->count();

            // Calculate statistics
            if ($vitalRecords->isNotEmpty()) {
                $vitalStats = [
                    'temperature' => [
                        'min' => round($vitalRecords->whereNotNull('temperature')->min('temperature'), 1),
                        'max' => round($vitalRecords->whereNotNull('temperature')->max('temperature'), 1),
                        'avg' => round($vitalRecords->whereNotNull('temperature')->avg('temperature'), 1),
                    ],
                    'heart_rate' => [
                        'min' => $vitalRecords->whereNotNull('heart_rate')->min('heart_rate'),
                        'max' => $vitalRecords->whereNotNull('heart_rate')->max('heart_rate'),
                        'avg' => round($vitalRecords->whereNotNull('heart_rate')->avg('heart_rate')),
                    ],
                    'oxygen_saturation' => [
                        'min' => $vitalRecords->whereNotNull('oxygen_saturation')->min('oxygen_saturation'),
                        'max' => $vitalRecords->whereNotNull('oxygen_saturation')->max('oxygen_saturation'),
                        'avg' => round($vitalRecords->whereNotNull('oxygen_saturation')->avg('oxygen_saturation')),
                    ],
                ];
            }

            // Generate AI insights
            $aiInsights = $this->generateAIInsights($vitalRecords, $latestVital);
        }

        // Prepare chart data
        $chartData = $this->prepareChartData($vitalRecords, $timeRange);

        // ✅ UPDATED: Calculate stats only for assigned patients
        $overviewStats = [
            'total_patients' => $patientsWithVitals->count(),
            'critical_patients' => Patient::whereHas('appointments', function ($query) use ($assignedDoctorIds) {
                $query->whereIn('doctor_id', $assignedDoctorIds);
            })
                ->whereHas('vitalRecords', function ($query) {
                    $query->where('is_critical', true)
                        ->where('recorded_at', '>=', Carbon::now()->subHours(24));
                })->count(),
            'recent_readings' => VitalRecord::whereIn('patient_id', $patientsWithVitals->pluck('patient_id'))
                ->whereDate('recorded_at', Carbon::today())
                ->count(),
        ];

        return view('nurse.nurse_vitalSignsTrendsAnalytics', compact(
            'nurse',
            'patientsWithVitals',
            'patient',
            'vitalRecords',
            'latestVital',
            'vitalStats',
            'aiInsights',
            'selectedPatientId',
            'timeRange',
            'vitalType',
            'chartData',
            'criticalReadingsCount',
            'overviewStats'
        ));
    }

    /**
     * Get start date based on time range
     */
    private function getStartDate($timeRange)
    {
        return match ($timeRange) {
            '24h' => Carbon::now()->subHours(24),
            '48h' => Carbon::now()->subHours(48),
            '7d' => Carbon::now()->subDays(7),
            '30d' => Carbon::now()->subDays(30),
            default => Carbon::now()->subDays(7),
        };
    }

    /**
     * Generate AI-powered clinical insights
     */
    private function generateAIInsights($vitalRecords, $latestVital)
    {
        $insights = [];

        if (!$latestVital || $vitalRecords->count() < 2) {
            return $insights;
        }

        // Check SpO2 trend
        $recentSpO2 = $vitalRecords->where('oxygen_saturation', '!=', null)
            ->sortByDesc('recorded_at')
            ->take(6)
            ->pluck('oxygen_saturation');

        if ($recentSpO2->count() >= 2) {
            $firstReading = $recentSpO2->last();
            $lastReading = $recentSpO2->first();
            $decrease = $firstReading - $lastReading;

            if ($decrease >= 5 && $lastReading < 95) {
                $insights[] = [
                    'type' => 'critical',
                    'icon' => '⚠️',
                    'title' => 'CRITICAL ALERT',
                    'message' => "SpO2 decreased by {$decrease}% (from {$firstReading}% to {$lastReading}%). Immediate oxygen therapy assessment recommended."
                ];
            } elseif ($lastReading >= 95 && $lastReading <= 100) {
                $insights[] = [
                    'type' => 'positive',
                    'icon' => '✓',
                    'title' => 'POSITIVE',
                    'message' => "Oxygen saturation stable within normal range ({$lastReading}%)."
                ];
            }
        }

        // Check heart rate variability
        $recentHR = $vitalRecords->where('heart_rate', '!=', null)
            ->sortByDesc('recorded_at')
            ->take(6)
            ->pluck('heart_rate');

        if ($recentHR->count() >= 2) {
            $variability = $recentHR->max() - $recentHR->min();
            if ($variability > 30) {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => '⚡',
                    'title' => 'TRENDING',
                    'message' => "Significant heart rate variability detected (range: {$recentHR->min()}-{$recentHR->max()} bpm). Consider cardiac monitoring and electrolyte panel."
                ];
            }
        }

        // Check blood pressure stability
        if ($latestVital->blood_pressure) {
            $bpParts = explode('/', $latestVital->blood_pressure);
            if (count($bpParts) == 2) {
                $systolic = (int)$bpParts[0];
                $diastolic = (int)$bpParts[1];

                if ($systolic >= 120 && $systolic <= 140 && $diastolic >= 80 && $diastolic <= 90) {
                    $insights[] = [
                        'type' => 'positive',
                        'icon' => '✓',
                        'title' => 'POSITIVE',
                        'message' => "Blood pressure stabilized within acceptable range. Current medication regime appears effective."
                    ];
                } elseif ($systolic > 140 || $diastolic > 90) {
                    $insights[] = [
                        'type' => 'warning',
                        'icon' => '⚠️',
                        'title' => 'ELEVATED',
                        'message' => "Blood pressure elevated ({$latestVital->blood_pressure}). Monitor closely and consider medication adjustment."
                    ];
                }
            }
        }

        // Temperature trend
        $recentTemp = $vitalRecords->where('temperature', '!=', null)
            ->sortByDesc('recorded_at')
            ->take(3)
            ->pluck('temperature');

        if ($recentTemp->count() >= 2 && $recentTemp->max() > 38) {
            $insights[] = [
                'type' => 'warning',
                'icon' => '🌡️',
                'title' => 'ELEVATED TEMPERATURE',
                'message' => "Temperature trending upward (current: {$recentTemp->first()}°C). Monitor for signs of infection."
            ];
        }

        // Predictive insight if critical vitals
        if ($latestVital->is_critical) {
            $insights[] = [
                'type' => 'predictive',
                'icon' => '📊',
                'title' => 'PREDICTIVE',
                'message' => "Based on current vital trends, recommend increased monitoring frequency (every 2 hours) and continuous observation."
            ];
        }

        // Overall stability assessment
        $criticalCount = $vitalRecords->where('is_critical', true)->count();
        $totalCount = $vitalRecords->count();

        if ($criticalCount == 0 && $totalCount >= 3) {
            $insights[] = [
                'type' => 'positive',
                'icon' => '🎯',
                'title' => 'STABLE TREND',
                'message' => "All vital signs within normal parameters over the past {$totalCount} readings. Patient showing stable progression."
            ];
        }

        return $insights;
    }

    /**
     * Prepare data for charts
     */
    private function prepareChartData($vitalRecords, $timeRange)
    {
        if ($vitalRecords->isEmpty()) {
            return [
                'labels' => [],
                'temperature' => [],
                'heart_rate' => [],
                'blood_pressure_sys' => [],
                'blood_pressure_dia' => [],
                'oxygen_saturation' => [],
            ];
        }

        $labels = [];
        $temperature = [];
        $heartRate = [];
        $bpSystolic = [];
        $bpDiastolic = [];
        $oxygenSaturation = [];

        foreach ($vitalRecords as $vital) {
            // Format label based on time range
            if ($timeRange === '24h' || $timeRange === '48h') {
                $labels[] = $vital->recorded_at->format('H:i');
            } else {
                $labels[] = $vital->recorded_at->format('M d');
            }

            $temperature[] = $vital->temperature ?? null;
            $heartRate[] = $vital->heart_rate ?? null;
            $oxygenSaturation[] = $vital->oxygen_saturation ?? null;

            // Parse blood pressure
            if ($vital->blood_pressure) {
                $bp = explode('/', $vital->blood_pressure);
                $bpSystolic[] = isset($bp[0]) ? (int)$bp[0] : null;
                $bpDiastolic[] = isset($bp[1]) ? (int)$bp[1] : null;
            } else {
                $bpSystolic[] = null;
                $bpDiastolic[] = null;
            }
        }

        return [
            'labels' => $labels,
            'temperature' => $temperature,
            'heart_rate' => $heartRate,
            'blood_pressure_sys' => $bpSystolic,
            'blood_pressure_dia' => $bpDiastolic,
            'oxygen_saturation' => $oxygenSaturation,
        ];
    }

    /**
     * Export analytics report
     */
    public function exportReport($patientId, Request $request)
    {
        // ✅ SECURITY CHECK before export
        $nurse = auth()->user()->nurse;
        $assignedDoctorIds = $nurse->assignedDoctors()->pluck('doctor_id');

        $hasAccess = Patient::where('patient_id', $patientId)
            ->whereHas('appointments', function ($query) use ($assignedDoctorIds) {
                $query->whereIn('doctor_id', $assignedDoctorIds);
            })
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Access Denied: You can only export reports for patients assigned to your doctors.');
        }

        // Implement PDF/Excel export logic here
        return response()->json([
            'message' => 'Export functionality coming soon',
            'patient_id' => $patientId
        ]);
    }
}
