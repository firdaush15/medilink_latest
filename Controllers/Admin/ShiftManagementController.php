<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffShift;
use App\Models\ShiftTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShiftManagementController extends Controller
{
    /**
     * Display weekly shift calendar with enhanced filtering
     */
    public function index(Request $request)
    {
        $weekStart = $request->get('week_start', now()->startOfWeek());
        $weekStart = Carbon::parse($weekStart);
        $weekEnd = $weekStart->copy()->endOfWeek();

        // Get filters
        $viewMode = $request->get('view_mode', 'grid'); // grid or compact
        $roleFilter = $request->get('role_filter', 'all');
        $searchQuery = $request->get('search');
        $perPage = $request->get('per_page', 15);

        // Build staff query with filters
        $staffQuery = User::whereIn('role', ['doctor', 'nurse', 'pharmacist', 'receptionist'])
            ->orderBy('role')
            ->orderBy('name');

        // Apply role filter
        if ($roleFilter !== 'all') {
            $staffQuery->where('role', $roleFilter);
        }

        // Apply search filter
        if ($searchQuery) {
            $staffQuery->where('name', 'LIKE', "%{$searchQuery}%");
        }

        // Get paginated staff
        $staff = $staffQuery->paginate($perPage);

        // Get all shifts for this week
        $shifts = StaffShift::with(['user', 'template'])
            ->whereBetween('shift_date', [$weekStart, $weekEnd])
            ->orderBy('shift_date')
            ->orderBy('start_time')
            ->get();

        // Get shift templates
        $templates = ShiftTemplate::where('is_active', true)->get();

        // Calculate stats
        $stats = $this->calculateShiftStats($shifts);

        // Calculate per-staff statistics for compact view
        $staffStats = [];
        if ($viewMode === 'compact') {
            foreach ($staff as $person) {
                $staffStats[$person->id] = $this->calculateStaffWeekStats($person->id, $shifts, $weekStart, $weekEnd);
            }
        }

        // Get role counts for filter badges
        $roleCounts = User::whereIn('role', ['doctor', 'nurse', 'pharmacist', 'receptionist'])
            ->selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        return view('admin.admin_shiftManagement', compact(
            'shifts',
            'staff',
            'templates',
            'weekStart',
            'weekEnd',
            'stats',
            'viewMode',
            'roleFilter',
            'searchQuery',
            'staffStats',
            'roleCounts'
        ));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $staff = User::whereIn('role', ['doctor', 'nurse', 'pharmacist', 'receptionist'])
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $templates = ShiftTemplate::where('is_active', true)->get();

        return view('admin.admin_shiftManagement_create', compact('staff', 'templates'));
    }

    /**
     * Store new shift
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_date' => 'required|date|after_or_equal:today',
            'template_id' => 'nullable|exists:shift_templates,template_id',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'is_recurring' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = User::findOrFail($validated['user_id']);

        // Check for conflicts
        $conflict = $this->checkShiftConflict(
            $validated['user_id'],
            $validated['shift_date'],
            $validated['start_time'],
            $validated['end_time']
        );

        if ($conflict) {
            return back()->withErrors(['error' => 'Shift conflict detected! This staff member already has a shift at this time.']);
        }

        // Create shift
        $shift = StaffShift::create([
            'user_id' => $validated['user_id'],
            'staff_role' => $user->role,
            'template_id' => $validated['template_id'] ?? null,
            'shift_date' => $validated['shift_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'scheduled',
            'assigned_by' => auth()->id(),
            'is_recurring' => $validated['is_recurring'] ?? false,
            'notes' => $validated['notes'] ?? null,
        ]);

        Log::info("✅ Shift created: {$user->name} on {$validated['shift_date']} ({$validated['start_time']} - {$validated['end_time']})");

        // If recurring, create future shifts
        if ($shift->is_recurring) {
            $this->createRecurringShifts($shift);
        }

        return redirect()->route('admin.shifts.index')->with('success', 'Shift created successfully!');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $shift = StaffShift::with('user')->findOrFail($id);
        $templates = ShiftTemplate::where('is_active', true)->get();

        return view('admin.admin_shiftManagement_edit', compact('shift', 'templates'));
    }

    /**
     * Update shift
     */
    public function update(Request $request, $id)
    {
        $shift = StaffShift::findOrFail($id);

        $validated = $request->validate([
            'shift_date' => 'required|date',
            'template_id' => 'nullable|exists:shift_templates,template_id',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'status' => 'required|in:scheduled,checked_in,checked_out,absent,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check for conflicts (excluding current shift)
        $conflict = $this->checkShiftConflict(
            $shift->user_id,
            $validated['shift_date'],
            $validated['start_time'],
            $validated['end_time'],
            $id
        );

        if ($conflict) {
            return back()->withErrors(['error' => 'Shift conflict detected with another shift!']);
        }

        // Log status changes
        if ($shift->status !== $validated['status']) {
            Log::info("Shift status changed: {$shift->user->name} from '{$shift->status}' to '{$validated['status']}'");
            
            if ($validated['status'] === 'checked_in' && !$shift->actual_check_in) {
                $validated['actual_check_in'] = now();
            }
            
            if ($validated['status'] === 'checked_out' && !$shift->actual_check_out) {
                $validated['actual_check_out'] = now();
            }
        }

        $shift->update($validated);

        return redirect()->route('admin.shifts.index')->with('success', 'Shift updated successfully!');
    }

    /**
     * Delete shift
     */
    public function destroy($id)
    {
        $shift = StaffShift::findOrFail($id);
        
        Log::info("🗑️ Shift deleted: {$shift->user->name} on {$shift->shift_date->format('Y-m-d')}");
        
        $shift->delete();

        return redirect()->route('admin.shifts.index')->with('success', 'Shift deleted successfully!');
    }

    /**
     * Check for shift conflicts
     */
    private function checkShiftConflict($userId, $date, $startTime, $endTime, $excludeShiftId = null)
    {
        $query = StaffShift::where('user_id', $userId)
            ->where('shift_date', $date)
            ->where('status', '!=', 'cancelled');

        if ($excludeShiftId) {
            $query->where('shift_id', '!=', $excludeShiftId);
        }

        return $query->where(function ($q) use ($startTime, $endTime) {
            $q->whereBetween('start_time', [$startTime, $endTime])
                ->orWhereBetween('end_time', [$startTime, $endTime])
                ->orWhere(function ($subQuery) use ($startTime, $endTime) {
                    $subQuery->where('start_time', '<=', $startTime)
                        ->where('end_time', '>=', $endTime);
                });
        })->exists();
    }

    /**
     * Create recurring shifts (weekly for 3 months)
     */
    private function createRecurringShifts($baseShift)
    {
        $endDate = Carbon::parse($baseShift->shift_date)->addMonths(3);
        $currentDate = Carbon::parse($baseShift->shift_date)->addWeek();
        
        $createdCount = 0;
        
        while ($currentDate <= $endDate) {
            $exists = StaffShift::where('user_id', $baseShift->user_id)
                ->where('shift_date', $currentDate->format('Y-m-d'))
                ->exists();
            
            if (!$exists) {
                StaffShift::create([
                    'user_id' => $baseShift->user_id,
                    'staff_role' => $baseShift->staff_role,
                    'template_id' => $baseShift->template_id,
                    'shift_date' => $currentDate->format('Y-m-d'),
                    'start_time' => $baseShift->start_time,
                    'end_time' => $baseShift->end_time,
                    'status' => 'scheduled',
                    'notes' => $baseShift->notes,
                    'assigned_by' => auth()->id(),
                    'is_recurring' => true,
                    'recurrence_pattern' => 'weekly',
                ]);
                
                $createdCount++;
            }
            
            $currentDate->addWeek();
        }
        
        Log::info("✅ Created {$createdCount} recurring shifts for {$baseShift->user->name}");
    }

    /**
     * Calculate accurate shift statistics
     */
    private function calculateShiftStats($shifts)
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        
        $totalShifts = $shifts->count();
        
        $doctorsOnDuty = $shifts->filter(function($shift) use ($currentTime) {
            return $shift->shift_date->isToday() &&
                   $shift->staff_role === 'doctor' &&
                   $shift->status === 'checked_in' &&
                   $shift->start_time->format('H:i:s') <= $currentTime &&
                   $shift->end_time->format('H:i:s') >= $currentTime;
        })->count();
        
        $nursesOnDuty = $shifts->filter(function($shift) use ($currentTime) {
            return $shift->shift_date->isToday() &&
                   $shift->staff_role === 'nurse' &&
                   $shift->status === 'checked_in' &&
                   $shift->start_time->format('H:i:s') <= $currentTime &&
                   $shift->end_time->format('H:i:s') >= $currentTime;
        })->count();
        
        $scheduledToday = $shifts->where('shift_date', today())->count();
        $checkedInToday = $shifts->where('shift_date', today())
            ->where('status', 'checked_in')
            ->count();
        
        $coverageRate = $scheduledToday > 0 
            ? round(($checkedInToday / $scheduledToday) * 100) 
            : 100;
        
        return [
            'total_shifts' => $totalShifts,
            'doctors_on_duty' => $doctorsOnDuty,
            'nurses_on_duty' => $nursesOnDuty,
            'coverage_rate' => $coverageRate,
        ];
    }

    /**
     * NEW: Calculate per-staff weekly statistics
     */
    private function calculateStaffWeekStats($userId, $shifts, $weekStart, $weekEnd)
    {
        $staffShifts = $shifts->where('user_id', $userId);
        
        $totalShifts = $staffShifts->count();
        $totalHours = 0;
        $checkedIn = 0;
        $scheduled = 0;
        $absent = 0;
        
        foreach ($staffShifts as $shift) {
            // Calculate hours
            $start = Carbon::parse($shift->start_time);
            $end = Carbon::parse($shift->end_time);
            $totalHours += $start->diffInHours($end);
            
            // Count statuses
            if ($shift->status === 'checked_in' || $shift->status === 'checked_out') {
                $checkedIn++;
            } elseif ($shift->status === 'scheduled') {
                $scheduled++;
            } elseif ($shift->status === 'absent') {
                $absent++;
            }
        }
        
        return [
            'total_shifts' => $totalShifts,
            'total_hours' => $totalHours,
            'checked_in' => $checkedIn,
            'scheduled' => $scheduled,
            'absent' => $absent,
        ];
    }
}