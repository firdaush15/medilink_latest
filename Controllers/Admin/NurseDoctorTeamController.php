<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NurseDoctorAssignment;
use App\Models\Doctor;
use App\Models\Nurse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NurseDoctorTeamController extends Controller
{
    /**
     * Display team management dashboard
     */
    public function index(Request $request)
    {
        // Get filter params
        $doctorFilter = $request->get('doctor_id');
        $statusFilter = $request->get('status', 'active');
        
        // Get all doctors with their assignments
        $doctors = Doctor::with(['user', 'assignedNurses' => function($query) use ($statusFilter) {
                if ($statusFilter === 'active') {
                    $query->where('nurse_doctor_assignments.is_active', true);
                } elseif ($statusFilter === 'inactive') {
                    $query->where('nurse_doctor_assignments.is_active', false);
                }
                $query->orderBy('nurse_doctor_assignments.priority_order');
            }, 'assignedNurses.user'])
            ->when($doctorFilter, function($q) use ($doctorFilter) {
                $q->where('doctor_id', $doctorFilter);
            })
            ->whereHas('user', function($q) {
                $q->whereIn('role', ['doctor']);
            })
            ->get();

        // Get all available nurses (not assigned or have capacity)
        $availableNurses = Nurse::with('user')
            ->whereDoesntHave('doctorAssignments', function($q) use ($statusFilter) {
                $q->where('is_active', true);
            })
            ->orWhereHas('doctorAssignments', function($q) {
                $q->where('is_active', true)
                  ->havingRaw('COUNT(*) < 3'); // Max 3 doctors per nurse
            })
            ->get();

        // Calculate statistics
        $stats = $this->calculateTeamStats();

        // Get all doctors for filter dropdown
        $allDoctors = Doctor::with('user')->get();

        return view('admin.admin_teamManagement', compact(
            'doctors',
            'availableNurses',
            'stats',
            'allDoctors',
            'doctorFilter',
            'statusFilter'
        ));
    }

    /**
     * Show assignment creation form
     */
    public function create(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        
        // Get doctor
        $doctor = null;
        if ($doctorId) {
            $doctor = Doctor::with('user')->findOrFail($doctorId);
        }

        // Get all doctors
        $doctors = Doctor::with('user')
            ->whereHas('user')
            ->orderBy('user_id')
            ->get();

        // Get available nurses
        $nurses = Nurse::with('user')
            ->whereIn('availability_status', ['Available', 'On Duty'])
            ->get();

        return view('admin.admin_teamManagement_create', compact(
            'doctors',
            'nurses',
            'doctor'
        ));
    }

    /**
     * Store new nurse-doctor assignment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nurse_id' => 'required|exists:nurses,nurse_id',
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'assignment_type' => 'required|in:primary,backup,floater',
            'priority_order' => 'nullable|integer|min:1|max:10',
            'working_days' => 'nullable|array',
            'working_days.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'shift_start' => 'nullable|date_format:H:i',
            'shift_end' => 'nullable|date_format:H:i|after:shift_start',
            'assigned_from' => 'nullable|date|after_or_equal:today',
            'assigned_until' => 'nullable|date|after:assigned_from',
        ]);

        DB::beginTransaction();
        
        try {
            // Check for existing active assignment
            $exists = NurseDoctorAssignment::where('nurse_id', $validated['nurse_id'])
                ->where('doctor_id', $validated['doctor_id'])
                ->where('is_active', true)
                ->exists();

            if ($exists) {
                return back()->withErrors(['error' => 'This nurse is already assigned to this doctor!']);
            }

            // Get next priority order if not specified
            if (!isset($validated['priority_order'])) {
                $maxPriority = NurseDoctorAssignment::where('doctor_id', $validated['doctor_id'])
                    ->where('is_active', true)
                    ->max('priority_order');
                
                $validated['priority_order'] = ($maxPriority ?? 0) + 1;
            }

            // Create assignment
            $assignment = NurseDoctorAssignment::create([
                'nurse_id' => $validated['nurse_id'],
                'doctor_id' => $validated['doctor_id'],
                'assignment_type' => $validated['assignment_type'],
                'priority_order' => $validated['priority_order'],
                'working_days' => $validated['working_days'] ?? null,
                'shift_start' => $validated['shift_start'] ?? null,
                'shift_end' => $validated['shift_end'] ?? null,
                'is_active' => true,
                'assigned_from' => $validated['assigned_from'] ?? now(),
                'assigned_until' => $validated['assigned_until'] ?? null,
            ]);

            Log::info("✅ Nurse-Doctor assignment created: Nurse {$assignment->nurse->user->name} → Doctor {$assignment->doctor->user->name}");

            DB::commit();

            return redirect()->route('admin.teams.index')
                ->with('success', 'Nurse assigned to doctor successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Assignment creation failed: " . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create assignment: ' . $e->getMessage()]);
        }
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $assignment = NurseDoctorAssignment::with(['nurse.user', 'doctor.user'])
            ->findOrFail($id);

        return view('admin.admin_teamManagement_edit', compact('assignment'));
    }

    /**
     * Update assignment
     */
    public function update(Request $request, $id)
    {
        $assignment = NurseDoctorAssignment::findOrFail($id);

        $validated = $request->validate([
            'assignment_type' => 'required|in:primary,backup,floater',
            'priority_order' => 'required|integer|min:1|max:10',
            'working_days' => 'nullable|array',
            'shift_start' => 'nullable|date_format:H:i',
            'shift_end' => 'nullable|date_format:H:i|after:shift_start',
            'is_active' => 'required|boolean',
            'assigned_until' => 'nullable|date|after:assigned_from',
        ]);

        $assignment->update([
            'assignment_type' => $validated['assignment_type'],
            'priority_order' => $validated['priority_order'],
            'working_days' => $validated['working_days'] ?? null,
            'shift_start' => $validated['shift_start'] ?? null,
            'shift_end' => $validated['shift_end'] ?? null,
            'is_active' => $validated['is_active'],
            'assigned_until' => $validated['assigned_until'] ?? null,
        ]);

        Log::info("✅ Assignment updated: ID {$id}");

        return redirect()->route('admin.teams.index')
            ->with('success', 'Assignment updated successfully!');
    }

    /**
     * Delete assignment
     */
    public function destroy($id)
    {
        $assignment = NurseDoctorAssignment::findOrFail($id);
        
        Log::info("🗑️ Assignment deleted: Nurse {$assignment->nurse->user->name} ← Doctor {$assignment->doctor->user->name}");
        
        $assignment->delete();

        return redirect()->route('admin.teams.index')
            ->with('success', 'Assignment removed successfully!');
    }

    /**
     * Deactivate assignment (soft disable)
     */
    public function deactivate($id)
    {
        $assignment = NurseDoctorAssignment::findOrFail($id);
        
        $assignment->update([
            'is_active' => false,
            'assigned_until' => now(),
        ]);

        Log::info("⏸️ Assignment deactivated: ID {$id}");

        return redirect()->route('admin.teams.index')
            ->with('success', 'Assignment deactivated successfully!');
    }

    /**
     * Bulk assign nurses to a doctor
     */
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'nurse_ids' => 'required|array|min:1',
            'nurse_ids.*' => 'exists:nurses,nurse_id',
            'assignment_type' => 'required|in:primary,backup,floater',
        ]);

        DB::beginTransaction();
        
        try {
            $created = 0;
            $skipped = 0;
            
            foreach ($validated['nurse_ids'] as $index => $nurseId) {
                // Check if already assigned
                $exists = NurseDoctorAssignment::where('nurse_id', $nurseId)
                    ->where('doctor_id', $validated['doctor_id'])
                    ->where('is_active', true)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Create assignment
                NurseDoctorAssignment::create([
                    'nurse_id' => $nurseId,
                    'doctor_id' => $validated['doctor_id'],
                    'assignment_type' => $validated['assignment_type'],
                    'priority_order' => $index + 1,
                    'is_active' => true,
                    'assigned_from' => now(),
                ]);

                $created++;
            }

            DB::commit();

            $message = "Bulk assignment completed: {$created} nurses assigned";
            if ($skipped > 0) {
                $message .= ", {$skipped} skipped (already assigned)";
            }

            Log::info("✅ " . $message);

            return redirect()->route('admin.teams.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Bulk assignment failed: " . $e->getMessage());
            return back()->withErrors(['error' => 'Bulk assignment failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Calculate team statistics
     */
    private function calculateTeamStats()
    {
        $totalDoctors = Doctor::whereHas('user')->count();
        $totalNurses = Nurse::whereHas('user')->count();
        
        $doctorsWithNurses = Doctor::whereHas('assignedNurses', function($q) {
            $q->where('is_active', true);
        })->count();

        $nursesAssigned = Nurse::whereHas('doctorAssignments', function($q) {
            $q->where('is_active', true);
        })->count();

        $activeAssignments = NurseDoctorAssignment::where('is_active', true)->count();

        // Coverage rate
        $coverageRate = $totalDoctors > 0 
            ? round(($doctorsWithNurses / $totalDoctors) * 100) 
            : 0;

        // Utilization rate
        $utilizationRate = $totalNurses > 0
            ? round(($nursesAssigned / $totalNurses) * 100)
            : 0;

        return [
            'total_doctors' => $totalDoctors,
            'total_nurses' => $totalNurses,
            'doctors_with_nurses' => $doctorsWithNurses,
            'nurses_assigned' => $nursesAssigned,
            'active_assignments' => $activeAssignments,
            'coverage_rate' => $coverageRate,
            'utilization_rate' => $utilizationRate,
        ];
    }
}