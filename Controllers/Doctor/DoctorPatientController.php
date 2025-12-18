<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DoctorPatientController extends Controller
{
    /**
     * Display list of patients that this doctor has treated or will treat
     * REAL-WORLD SCENARIO: Only show patients with:
     * 1. Completed appointments (past medical history)
     * 2. Active care relationship (within last 2 years)
     * 3. Upcoming scheduled appointments
     */
    public function index(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        if (!$doctor) {
            return redirect()->route('doctor.dashboard')->with('error', 'Doctor profile not found');
        }

        // ✅ REAL-WORLD FILTER: Only patients with active care relationship
        $query = Patient::with(['user', 'appointments' => function($q) use ($doctor) {
            $q->where('doctor_id', $doctor->doctor_id)
              ->orderBy('appointment_date', 'desc');
        }])
        ->whereHas('appointments', function($q) use ($doctor) {
            $q->where('doctor_id', $doctor->doctor_id)
              ->where(function($subQuery) {
                  // ✅ FIXED: Proper OR logic
                  $subQuery
                      // Case 1: Completed appointments within last 2 years (medical records retention)
                      ->where(function($completedQuery) {
                          $completedQuery->where('status', 'completed')
                                       ->where('appointment_date', '>=', Carbon::now()->subYears(2));
                      })
                      // OR Case 2: Any confirmed/upcoming appointments (from today onwards)
                      ->orWhere(function($upcomingQuery) {
                          $upcomingQuery->whereIn('status', [
                                  'confirmed', 
                                  'checked_in', 
                                  'vitals_pending', 
                                  'vitals_recorded', 
                                  'ready_for_doctor', 
                                  'in_consultation'
                              ])
                              ->where('appointment_date', '>=', Carbon::today());
                      });
              });
        });

        // Apply search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            })->orWhere('phone_number', 'like', '%' . $search . '%');
        }

        // Apply gender filter
        if ($request->has('gender') && $request->gender != '') {
            $query->where('gender', $request->gender);
        }

        // ✅ NEW: Filter by care status
        if ($request->has('status') && $request->status != '') {
            switch ($request->status) {
                case 'active': // Has upcoming appointment
                    $query->whereHas('appointments', function($q) use ($doctor) {
                        $q->where('doctor_id', $doctor->doctor_id)
                          ->whereIn('status', ['confirmed', 'checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
                          ->where('appointment_date', '>=', Carbon::now());
                    });
                    break;
                case 'recent': // Seen within last 30 days
                    $query->whereHas('appointments', function($q) use ($doctor) {
                        $q->where('doctor_id', $doctor->doctor_id)
                          ->where('status', 'completed')
                          ->where('appointment_date', '>=', Carbon::now()->subDays(30));
                    });
                    break;
                case 'followup': // Needs follow-up (completed but has upcoming)
                    $query->whereHas('appointments', function($q) use ($doctor) {
                        $q->where('doctor_id', $doctor->doctor_id)
                          ->where('status', 'completed')
                          ->where('appointment_date', '>=', Carbon::now()->subDays(90));
                    })->whereHas('appointments', function($q) use ($doctor) {
                        $q->where('doctor_id', $doctor->doctor_id)
                          ->whereIn('status', ['confirmed'])
                          ->where('appointment_date', '>=', Carbon::now());
                    });
                    break;
                case 'historical': // Not seen in 6+ months
                    $query->whereHas('appointments', function($q) use ($doctor) {
                        $q->where('doctor_id', $doctor->doctor_id)
                          ->where('status', 'completed');
                    })->whereDoesntHave('appointments', function($q) use ($doctor) {
                        $q->where('doctor_id', $doctor->doctor_id)
                          ->where('appointment_date', '>=', Carbon::now()->subMonths(6));
                    });
                    break;
            }
        }

        // Apply sorting
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'name-asc':
                    $query->join('users', 'patients.user_id', '=', 'users.id')
                          ->orderBy('users.name', 'asc')
                          ->select('patients.*');
                    break;
                case 'name-desc':
                    $query->join('users', 'patients.user_id', '=', 'users.id')
                          ->orderBy('users.name', 'desc')
                          ->select('patients.*');
                    break;
                case 'age-asc':
                    $query->orderBy('date_of_birth', 'desc');
                    break;
                case 'age-desc':
                    $query->orderBy('date_of_birth', 'asc');
                    break;
                case 'last-visit-recent':
                    // Most recently seen first
                    $query->withMax(['appointments as last_visit_date' => function($q) use ($doctor) {
                        $q->where('doctor_id', $doctor->doctor_id)
                          ->where('status', 'completed');
                    }], 'appointment_date')
                    ->orderByDesc('last_visit_date');
                    break;
                case 'last-visit-oldest':
                    // Oldest visit first (potential follow-up needed)
                    $query->withMax(['appointments as last_visit_date' => function($q) use ($doctor) {
                        $q->where('doctor_id', $doctor->doctor_id)
                          ->where('status', 'completed');
                    }], 'appointment_date')
                    ->orderBy('last_visit_date');
                    break;
            }
        } else {
            // Default: Most recent activity first
            $query->withMax(['appointments as last_visit_date' => function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->doctor_id);
            }], 'appointment_date')
            ->orderByDesc('last_visit_date');
        }

        $patients = $query->paginate(15)->withQueryString();

        // Get care relationship status for each patient
        foreach ($patients as $patient) {
            // Last completed visit
            $patient->last_visit = $patient->appointments
                ->where('status', 'completed')
                ->sortByDesc('appointment_date')
                ->first();
            
            // Next upcoming appointment (including today's appointments)
            $patient->next_appointment = $patient->appointments
                ->whereIn('status', ['confirmed', 'checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
                ->filter(function($apt) {
                    $today = \Carbon\Carbon::today('Asia/Kuala_Lumpur');
                    return $apt->appointment_date >= $today;
                })
                ->sortBy('appointment_date')
                ->first();
            
            // Total visits with THIS doctor
            $patient->total_visits = $patient->appointments
                ->where('status', 'completed')
                ->count();

            // ✅ Care relationship status
            $daysSinceLastVisit = $patient->last_visit ? 
                Carbon::parse($patient->last_visit->appointment_date)->diffInDays(now()) : 999;
            
            if ($patient->next_appointment) {
                $patient->care_status = 'active'; // Has upcoming appointment
                $patient->care_status_label = 'Active Care';
                $patient->care_status_class = 'status-active';
            } elseif ($daysSinceLastVisit <= 30) {
                $patient->care_status = 'recent'; // Seen within last month
                $patient->care_status_label = 'Recently Treated';
                $patient->care_status_class = 'status-recent';
            } elseif ($daysSinceLastVisit <= 180) {
                $patient->care_status = 'followup'; // Should consider follow-up
                $patient->care_status_label = 'Follow-up Due';
                $patient->care_status_class = 'status-followup';
            } else {
                $patient->care_status = 'historical'; // Historical record only
                $patient->care_status_label = 'Historical';
                $patient->care_status_class = 'status-historical';
            }
        }

        // ✅ Statistics for dashboard cards
        $stats = [
            'total_active' => Patient::whereHas('appointments', function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->doctor_id)
                  ->whereIn('status', ['confirmed', 'checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
                  ->where('appointment_date', '>=', Carbon::now());
            })->count(),

            'seen_this_month' => Patient::whereHas('appointments', function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->doctor_id)
                  ->where('status', 'completed')
                  ->whereBetween('appointment_date', [
                      Carbon::now()->startOfMonth(),
                      Carbon::now()->endOfMonth()
                  ]);
            })->count(),

            'followup_needed' => Patient::whereHas('appointments', function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->doctor_id)
                  ->where('status', 'completed')
                  ->whereBetween('appointment_date', [
                      Carbon::now()->subDays(90),
                      Carbon::now()->subDays(30)
                  ]);
            })->whereDoesntHave('appointments', function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->doctor_id)
                  ->whereIn('status', ['confirmed'])
                  ->where('appointment_date', '>=', Carbon::now());
            })->count(),

            'total_historical' => Patient::whereHas('appointments', function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->doctor_id)
                  ->where('status', 'completed')
                  ->where('appointment_date', '>=', Carbon::now()->subYears(2));
            })->count(),
        ];

        return view('doctor.doctor_patients', compact('patients', 'doctor', 'stats'));
    }

    /**
     * Show detailed view of a specific patient
     * ✅ SECURITY: Only allow viewing if doctor has treated this patient
     */
    public function show($id)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        // ✅ CRITICAL SECURITY CHECK: Only show if doctor has appointments with this patient
        $patient = Patient::with([
            'user',
            'allergies' => function($q) {
                $q->where('is_active', true)->orderByRaw("FIELD(severity, 'Life-threatening', 'Severe', 'Moderate', 'Mild')");
            },
            'vitalRecords' => function($q) use ($doctor) {
                $q->whereHas('appointment', function($subQ) use ($doctor) {
                    $subQ->where('doctor_id', $doctor->doctor_id);
                })->orderBy('recorded_at', 'desc')->take(10);
            },
            'appointments' => function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->doctor_id)
                  ->orderBy('appointment_date', 'desc');
            },
            'medicalRecords' => function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->doctor_id)
                  ->orderBy('record_date', 'desc');
            },
            'prescriptions' => function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->doctor_id)
                  ->with('items')
                  ->orderBy('prescribed_date', 'desc');
            }
        ])
        ->whereHas('appointments', function($q) use ($doctor) {
            $q->where('doctor_id', $doctor->doctor_id);
        })
        ->findOrFail($id);

        // ✅ Calculate care relationship details
        $lastVisit = $patient->appointments->where('status', 'completed')->first();
        $daysSinceLastVisit = $lastVisit ? 
            Carbon::parse($lastVisit->appointment_date)->diffInDays(now()) : null;

        $patient->care_relationship = [
            'first_visit' => $patient->appointments->sortBy('appointment_date')->first(),
            'last_visit' => $lastVisit,
            'next_appointment' => $patient->appointments
                ->whereIn('status', ['confirmed', 'checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
                ->where('appointment_date', '>=', Carbon::today()) // ✅ FIX: Use today() instead of now()
                ->sortBy('appointment_date')
                ->first(),
            'total_visits' => $patient->appointments->where('status', 'completed')->count(),
            'days_since_last_visit' => $daysSinceLastVisit,
            'years_under_care' => $patient->appointments->sortBy('appointment_date')->first() ? 
                Carbon::parse($patient->appointments->sortBy('appointment_date')->first()->appointment_date)->diffInYears(now()) : 0,
        ];

        return view('doctor.patient_detail', compact('patient', 'doctor'));
    }

    /**
     * Add clinical notes to patient (not editing demographics)
     */
    public function addNote(Request $request, $id)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        // ✅ SECURITY: Verify doctor has treated this patient
        $patient = Patient::whereHas('appointments', function($q) use ($doctor) {
            $q->where('doctor_id', $doctor->doctor_id);
        })->findOrFail($id);

        $validated = $request->validate([
            'note_type' => 'required|string',
            'note_content' => 'required|string',
        ]);

        // Create a medical record entry for clinical notes
        $patient->medicalRecords()->create([
            'doctor_id' => $doctor->doctor_id,
            'record_date' => now(),
            'record_type' => $validated['note_type'],
            'record_title' => 'Clinical Note',
            'description' => $validated['note_content'],
            'file_path' => null,
        ]);

        return redirect()->back()->with('success', 'Clinical note added successfully');
    }
}