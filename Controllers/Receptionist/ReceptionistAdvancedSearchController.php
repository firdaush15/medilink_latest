<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceptionistAdvancedSearchController extends Controller
{
    /**
     * Show advanced search form and results
     */
    public function index(Request $request)
    {
        $query = Patient::with('user');

        // Apply filters
        $this->applySearchFilters($query, $request);

        $results = $query->paginate(20)->appends($request->query());

        // Log search activity
        $this->logSearch($request, $results->total());

        return view('receptionist.receptionist_advancedSearch', compact('results'));
    }

    /**
     * Apply all search filters to query
     */
    private function applySearchFilters($query, Request $request)
    {
        // Text search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_id', 'LIKE', "%{$search}%")
                  ->orWhere('phone_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Gender filter
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Age range filter
        if ($request->filled('age_min') || $request->filled('age_max')) {
            $this->applyAgeFilter($query, $request);
        }

        // Flagged patients filter
        if ($request->filled('flagged')) {
            $query->where('is_flagged', $request->flagged);
        }

        // No-show count filter
        if ($request->filled('no_shows_min')) {
            $query->where('no_show_count', '>=', $request->no_shows_min);
        }

        // Registration date filters
        if ($request->filled('registered_after')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('created_at', '>=', $request->registered_after);
            });
        }

        if ($request->filled('registered_before')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('created_at', '<=', $request->registered_before);
            });
        }
    }

    /**
     * Apply age range filter
     */
    private function applyAgeFilter($query, Request $request)
    {
        $today = today();
        
        if ($request->filled('age_min')) {
            $maxDate = $today->copy()->subYears($request->age_min);
            $query->where('date_of_birth', '<=', $maxDate);
        }
        
        if ($request->filled('age_max')) {
            $minDate = $today->copy()->subYears($request->age_max + 1);
            $query->where('date_of_birth', '>=', $minDate);
        }
    }

    /**
     * Log search activity for analytics
     */
    private function logSearch(Request $request, $resultsCount)
    {
        try {
            DB::table('patient_search_history')->insert([
                'user_id' => auth()->id(),
                'search_query' => $request->search ?? '',
                'filters' => json_encode($request->except('search')),
                'results_count' => $resultsCount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail if table doesn't exist yet
            \Log::warning('Failed to log search history: ' . $e->getMessage());
        }
    }

    /**
     * Export search results to Excel
     */
    public function export(Request $request)
    {
        $query = Patient::with('user');
        $this->applySearchFilters($query, $request);
        $patients = $query->get();

        $filename = 'patient_search_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($patients) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Patient ID', 'Name', 'Email', 'Phone', 
                'Gender', 'Age', 'No-Shows', 'Flagged'
            ]);

            // Data
            foreach ($patients as $patient) {
                fputcsv($file, [
                    'P' . str_pad($patient->patient_id, 4, '0', STR_PAD_LEFT),
                    $patient->user->name,
                    $patient->user->email,
                    $patient->phone_number,
                    $patient->gender,
                    $patient->age ?? 'N/A',
                    $patient->no_show_count ?? 0,
                    $patient->is_flagged ? 'Yes' : 'No',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}