<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaffTask; // ✅ Changed from NurseTask
use App\Models\Patient;
use App\Models\Appointment;
use Carbon\Carbon;

class NurseTasksController extends Controller
{
    /**
     * Display all tasks assigned to the current nurse
     */
    public function index(Request $request)
    {
        $nurse = auth()->user()->nurse;

        if (!$nurse) {
            abort(403, 'Nurse profile not found.');
        }

        $filter = $request->get('filter', 'all');

        // ✅ Base query - using StaffTask with forStaff scope
        $tasksQuery = StaffTask::with([
            'patient.user',
            'assignedBy', // ✅ Changed from 'doctor.user'
            'appointment'
        ])
        ->forStaff(auth()->id(), 'nurse'); // ✅ Use scope instead of where clause

        // Apply filters
        switch ($filter) {
            case 'pending':
                $tasksQuery->pending(); // ✅ Use scope
                break;
            case 'in_progress':
                $tasksQuery->inProgress(); // ✅ Use scope
                break;
            case 'urgent':
                $tasksQuery->urgent(); // ✅ Use scope
                break;
            case 'overdue':
                $tasksQuery->overdue(); // ✅ Use scope
                break;
            case 'today':
                $tasksQuery->dueToday(); // ✅ Use scope
                break;
            case 'completed':
                $tasksQuery->completed() // ✅ Use scope
                          ->whereDate('completed_at', '>=', today()->subDays(7));
                break;
        }

        // Paginate
        $tasks = $tasksQuery->orderByRaw("
                CASE priority
                    WHEN 'Critical' THEN 1
                    WHEN 'Urgent' THEN 2
                    WHEN 'High' THEN 3
                    WHEN 'Normal' THEN 4
                    WHEN 'Low' THEN 5
                END
            ")
            ->orderByRaw("
                CASE status
                    WHEN 'in_progress' THEN 1
                    WHEN 'pending' THEN 2
                    WHEN 'completed' THEN 3
                    WHEN 'cancelled' THEN 4
                END
            ")
            ->orderBy('due_at', 'asc')
            ->paginate(20);

        // Add computed properties
        $tasks->getCollection()->transform(function($task) {
            $task->is_overdue = $task->isOverdue();
            $task->time_remaining = $task->time_remaining; // ✅ Already an accessor in model
            return $task;
        });

        // ✅ Calculate counts using scopes
        $counts = [
            'all' => StaffTask::forStaff(auth()->id(), 'nurse')
                ->active()
                ->count(),
            'pending' => StaffTask::forStaff(auth()->id(), 'nurse')
                ->pending()
                ->count(),
            'in_progress' => StaffTask::forStaff(auth()->id(), 'nurse')
                ->inProgress()
                ->count(),
            'urgent' => StaffTask::forStaff(auth()->id(), 'nurse')
                ->urgent()
                ->count(),
            'overdue' => StaffTask::forStaff(auth()->id(), 'nurse')
                ->overdue()
                ->count(),
            'today' => StaffTask::forStaff(auth()->id(), 'nurse')
                ->dueToday()
                ->count(),
            'completed_week' => StaffTask::forStaff(auth()->id(), 'nurse')
                ->completed()
                ->whereDate('completed_at', '>=', today()->subDays(7))
                ->count(),
        ];

        return view('nurse.nurse_tasks', compact('tasks', 'filter', 'counts'));
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, $id)
    {
        $task = StaffTask::findOrFail($id); // ✅ Changed to StaffTask
        $nurse = auth()->user()->nurse;

        // ✅ Verify this task belongs to the current nurse
        if ($task->assigned_to_id !== auth()->id() || $task->assigned_to_type !== 'nurse') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled', // ✅ Lowercase status
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            if ($validated['status'] === 'completed') {
                // ✅ Use StaffTask's complete method
                $task->complete($validated['notes'] ?? null);
            } else {
                $task->update([
                    'status' => $validated['status'],
                ]);
            }

            $message = match($validated['status']) {
                'in_progress' => 'Task marked as in progress.',
                'completed' => 'Task completed successfully!',
                'cancelled' => 'Task cancelled.',
                default => 'Task status updated.',
            };

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update task: ' . $e->getMessage());
        }
    }

    /**
     * Start a task (mark as in progress)
     */
    public function startTask(Request $request, $id)
    {
        $task = StaffTask::findOrFail($id);

        // Verify ownership
        if ($task->assigned_to_id !== auth()->id() || $task->assigned_to_type !== 'nurse') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        try {
            $task->start(); // ✅ Use StaffTask's start method
            return redirect()->back()->with('success', 'Task started!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Complete a task
     */
    public function completeTask(Request $request, $id)
    {
        $task = StaffTask::findOrFail($id); // ✅ Changed to StaffTask
        $nurse = auth()->user()->nurse;

        // ✅ Verify this task belongs to the current nurse
        if ($task->assigned_to_id !== auth()->id() || $task->assigned_to_type !== 'nurse') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'completion_notes' => 'nullable|string|max:1000',
            'task_data' => 'nullable|array', // ✅ Optional structured data
        ]);

        try {
            // ✅ Use StaffTask's complete method (auto-sends notification)
            $task->complete(
                $validated['completion_notes'] ?? null,
                $validated['task_data'] ?? null
            );

            return redirect()->back()->with('success', 'Task completed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to complete task: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a task
     */
    public function cancelTask(Request $request, $id)
    {
        $task = StaffTask::findOrFail($id);

        // Verify ownership
        if ($task->assigned_to_id !== auth()->id() || $task->assigned_to_type !== 'nurse') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            $task->cancel($validated['reason'] ?? 'Cancelled by nurse');
            return redirect()->back()->with('success', 'Task cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * View task details
     */
    public function show($id)
    {
        $task = StaffTask::with([
            'assignedBy',
            'assignedTo',
            'patient.user',
            'appointment',
            'prescription',
            'medicine'
        ])->findOrFail($id);

        // Verify ownership
        if ($task->assigned_to_id !== auth()->id() || $task->assigned_to_type !== 'nurse') {
            abort(403, 'Unauthorized');
        }

        return view('nurse.nurse_task_details', compact('task'));
    }
}