<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\NurseReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\VitalRecord;

class NurseReportsController extends Controller
{
    public function index(Request $request)
    {
        $nurse = auth()->user()->nurse;

        if (!$nurse) {
            abort(403, 'Nurse profile not found.');
        }

        $filter = $request->get('filter', 'all');

        // ========================================
        // STATISTICS - Simplified for Outpatient
        // ========================================
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $totalReports = NurseReport::where('nurse_id', $nurse->nurse_id)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        $incidentReports = NurseReport::where('nurse_id', $nurse->nurse_id)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where('report_type', 'incident')
            ->count();

        $clinicalNotes = NurseReport::where('nurse_id', $nurse->nurse_id)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where('report_type', 'clinical_note')
            ->count();

        // Calculate vitals documentation rate
        $vitalsRecorded = VitalRecord::where('nurse_id', $nurse->nurse_id)
            ->whereMonth('recorded_at', $currentMonth)
            ->whereYear('recorded_at', $currentYear)
            ->count();

        $appointmentsHandled = VitalRecord::where('nurse_id', $nurse->nurse_id)
            ->whereMonth('recorded_at', $currentMonth)
            ->whereYear('recorded_at', $currentYear)
            ->distinct('appointment_id')
            ->count('appointment_id');

        $documentationRate = $appointmentsHandled > 0 
            ? round(($vitalsRecorded / $appointmentsHandled) * 100, 1) 
            : 100;

        $stats = [
            'total_reports' => $totalReports,
            'incident_reports' => $incidentReports,
            'clinical_notes' => $clinicalNotes,
            'documentation_rate' => $documentationRate,
        ];

        // ========================================
        // REPORT CATEGORIES - Outpatient Focused
        // ========================================
        $categoryCounts = NurseReport::where('nurse_id', $nurse->nurse_id)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->select('report_type', DB::raw('count(*) as count'))
            ->groupBy('report_type')
            ->pluck('count', 'report_type')
            ->toArray();

        $categories = [
            'incident' => [
                'name' => 'Incident Reports',
                'count' => $categoryCounts['incident'] ?? 0,
                'icon' => '🚨',
                'description' => 'Falls, allergic reactions, adverse events during visit'
            ],
            'clinical_note' => [
                'name' => 'Clinical Notes',
                'count' => $categoryCounts['clinical_note'] ?? 0,
                'icon' => '📋',
                'description' => 'Pre-consultation observations and patient concerns'
            ],
            'follow_up' => [
                'name' => 'Follow-up Notes',
                'count' => $categoryCounts['follow_up'] ?? 0,
                'icon' => '📞',
                'description' => 'Post-visit follow-up calls and patient check-ins'
            ],
            'referral' => [
                'name' => 'Referral Notes',
                'count' => $categoryCounts['referral'] ?? 0,
                'icon' => '🏥',
                'description' => 'Patient referrals to specialists or emergency care'
            ],
        ];

        // ========================================
        // RECENT REPORTS - Database Query
        // ========================================
        $reportsQuery = NurseReport::with(['patient.user'])
            ->where('nurse_id', $nurse->nurse_id);

        if ($filter !== 'all') {
            $reportsQuery->where('report_type', $filter);
        }

        $recentReports = $reportsQuery
            ->orderBy('event_datetime', 'desc')
            ->paginate(10);

        // ========================================
        // AVAILABLE PATIENTS - Recent Patients Only
        // ========================================
        $patients = Patient::with('user')
            ->whereHas('appointments', function ($query) use ($nurse) {
                $query->whereDate('appointment_date', '>=', Carbon::now()->subDays(7))
                    ->where('status', '!=', 'cancelled');
            })
            ->orderBy('user_id')
            ->get();

        return view('nurse.nurse_reportsDocumentation', compact(
            'nurse',
            'stats',
            'recentReports',
            'patients',
            'categories',
            'filter'
        ));
    }

    public function store(Request $request)
    {
        $nurse = auth()->user()->nurse;

        $validated = $request->validate([
            'report_category' => 'required|string|in:incident,clinical_note,follow_up,referral',
            'patient_id' => 'required|exists:patients,patient_id',
            'event_datetime' => 'required|date',
            'severity' => 'nullable|in:minor,moderate,major,critical',
            'description' => 'required|string',
            'actions_taken' => 'nullable|string',
            'followup_required' => 'required|in:yes,no',
            'physician_notified' => 'required|in:yes,no,pending',
            'additional_notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $report = NurseReport::create([
                'nurse_id' => $nurse->nurse_id,
                'patient_id' => $validated['patient_id'],
                'report_type' => $validated['report_category'],
                'event_datetime' => $validated['event_datetime'],
                'location' => 'Outpatient Clinic', // Default for outpatient
                'severity' => $validated['severity'] ?? null,
                'description' => $validated['description'],
                'actions_taken' => $validated['actions_taken'] ?? null,
                'followup_required' => $validated['followup_required'] === 'yes',
                'physician_notified' => $validated['physician_notified'] === 'yes',
                'additional_notes' => $validated['additional_notes'] ?? null,
                'is_confidential' => false, // Not needed for outpatient
            ]);

            DB::commit();

            return redirect()->route('nurse.reports-documentation')
                ->with('success', "Report {$report->report_number} submitted successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to submit report: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $nurse = auth()->user()->nurse;

        $report = NurseReport::with(['patient.user', 'nurse.user'])
            ->where('nurse_id', $nurse->nurse_id)
            ->findOrFail($id);

        return view('nurse.nurse_reportDetail', compact('report'));
    }

    public function filterByCategory(Request $request, $category)
    {
        return redirect()->route('nurse.reports-documentation', ['filter' => $category]);
    }

    public function exportPdf($id)
    {
        // Optional: Implement if needed for clinic records
        return response()->json(['message' => 'PDF export available upon request']);
    }
}