<?php
// ============================================
// NURSE WORK DASHBOARD CONTROLLER
// app/Http/Controllers/Nurse/NurseWorkDashboardController.php
// ✅ UPDATED: Using StaffTask instead of NurseTask
// ============================================

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use App\Models\StaffTask; // ✅ Changed from NurseTask
use App\Models\StaffAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NurseWorkDashboardController extends Controller
{
    /**
     * Display unified work dashboard with tasks and alerts
     */
    public function index()
    {
        $nurse = Auth::user()->nurse;

        // Get counts for stats
        $counts = [
            'critical_alerts' => StaffAlert::where('recipient_id', Auth::id())
                ->where('recipient_type', 'nurse')
                ->where('priority', 'Critical')
                ->where('is_acknowledged', false)
                ->count(),
            
            'urgent_tasks' => StaffTask::forStaff(Auth::id(), 'nurse') // ✅ Use scope
                ->whereIn('priority', ['Urgent', 'Critical'])
                ->active()
                ->count(),
            
            'pending_tasks' => StaffTask::forStaff(Auth::id(), 'nurse') // ✅ Use scope
                ->pending()
                ->count(),
            
            'completed_today' => StaffTask::forStaff(Auth::id(), 'nurse') // ✅ Use scope
                ->completed()
                ->whereDate('completed_at', today())
                ->count(),
        ];

        // Combine tasks and alerts into unified work items
        $workItems = $this->getUnifiedWorkItems($nurse);

        // Get recent alerts for sidebar
        $recentAlerts = StaffAlert::where('recipient_id', Auth::id())
            ->where('recipient_type', 'nurse')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('nurse.nurse_workDashboard', compact('counts', 'workItems', 'recentAlerts'));
    }

    /**
     * Combine tasks and alerts into a single unified list
     * Sorted by priority and time
     */
    private function getUnifiedWorkItems($nurse)
    {
        $items = collect();

        // ✅ Get active tasks using StaffTask
        $tasks = StaffTask::with(['patient.user', 'assignedBy', 'appointment'])
            ->forStaff(Auth::id(), 'nurse')
            ->active()
            ->get();

        foreach ($tasks as $task) {
            $items->push([
                'id' => $task->task_id,
                'type' => 'task',
                'title' => $task->task_title,
                'message' => $task->task_description,
                'patient' => $task->patient ? $task->patient->user->name : null,
                'patient_id' => $task->patient_id,
                'doctor' => $task->assignedBy ? $task->assignedBy->name : null, // ✅ Changed
                'priority' => $task->priority,
                'priority_class' => $this->getPriorityClass($task->priority),
                'status' => $task->status,
                'due_time' => $task->due_at ? $task->due_at->format('h:i A') : null,
                'time_ago' => $task->created_at->diffForHumans(),
                'created_at' => $task->created_at,
                'priority_score' => $this->calculatePriorityScore('task', $task->priority, $task->created_at, $task->due_at),
                'is_read' => true, // Tasks don't have read status
                'action_url' => route('nurse.tasks.show', $task->task_id),
            ]);
        }

        // Get unacknowledged alerts
        $alerts = StaffAlert::with(['patient.user'])
            ->where('recipient_id', Auth::id())
            ->where('recipient_type', 'nurse')
            ->where('is_acknowledged', false)
            ->get();

        foreach ($alerts as $alert) {
            $items->push([
                'id' => $alert->alert_id,
                'type' => 'alert',
                'title' => $alert->alert_title,
                'message' => $alert->alert_message,
                'patient' => $alert->patient ? $alert->patient->user->name : null,
                'patient_id' => $alert->patient_id,
                'doctor' => null,
                'priority' => $alert->priority,
                'priority_class' => $this->getPriorityClass($alert->priority),
                'status' => null,
                'due_time' => null,
                'time_ago' => $alert->created_at->diffForHumans(),
                'created_at' => $alert->created_at,
                'priority_score' => $this->calculatePriorityScore('alert', $alert->priority, $alert->created_at),
                'is_read' => $alert->is_read,
                'action_url' => $alert->action_url,
            ]);
        }

        // Sort by priority score (highest first)
        return $items->sortByDesc('priority_score')->values();
    }

    /**
     * Calculate priority score for sorting
     * Higher score = more urgent
     */
    private function calculatePriorityScore($type, $priority, $createdAt, $dueAt = null)
    {
        $score = 0;

        // Base priority score
        $priorityScores = [
            'Critical' => 1000,
            'Urgent' => 800,
            'High' => 600,
            'Normal' => 400,
            'Low' => 200,
        ];
        $score += $priorityScores[$priority] ?? 400;

        // Age penalty (older items get higher score)
        $ageInMinutes = $createdAt->diffInMinutes(now());
        $score += min($ageInMinutes * 0.5, 200); // Cap at 200

        // Overdue penalty for tasks
        if ($type === 'task' && $dueAt && $dueAt->isPast()) {
            $score += 500; // Overdue tasks get massive boost
        }

        // Unread alerts get small boost
        if ($type === 'alert') {
            $score += 50;
        }

        return $score;
    }

    /**
     * Get CSS class for priority
     */
    private function getPriorityClass($priority)
    {
        return match ($priority) {
            'Critical' => 'critical',
            'Urgent' => 'urgent',
            default => '',
        };
    }

    /**
     * Start a task
     */
    public function startTask(Request $request, $taskId)
    {
        $task = StaffTask::findOrFail($taskId); // ✅ Changed to StaffTask

        // Verify ownership
        if ($task->assigned_to_id !== Auth::id() || $task->assigned_to_type !== 'nurse') {
            return back()->withErrors(['error' => 'This task is not assigned to you.']);
        }

        try {
            $task->start(); // ✅ Use StaffTask method
            return back()->with('success', 'Task started successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Complete a task
     */
    public function completeTask(Request $request, $taskId)
    {
        $task = StaffTask::findOrFail($taskId); // ✅ Changed to StaffTask

        // Verify ownership
        if ($task->assigned_to_id !== Auth::id() || $task->assigned_to_type !== 'nurse') {
            return back()->withErrors(['error' => 'This task is not assigned to you.']);
        }

        $validated = $request->validate([
            'completion_notes' => 'nullable|string|max:1000',
            'task_data' => 'nullable|array',
        ]);

        try {
            // ✅ Use StaffTask method (auto-sends notification)
            $task->complete(
                $validated['completion_notes'] ?? null,
                $validated['task_data'] ?? null
            );

            return redirect()
                ->route('nurse.work-dashboard')
                ->with('success', 'Task completed successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function refreshCounts()
    {
        $pendingTasks = StaffTask::where('assigned_to_id', Auth::id())
            ->where('assigned_to_type', 'nurse')
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();
            
        $unreadAlerts = StaffAlert::where('recipient_id', Auth::id())
            ->where('recipient_type', 'nurse')
            ->where('is_read', false)
            ->count();

        return response()->json([
            'pending_tasks' => $pendingTasks,
            'unread_alerts' => $unreadAlerts,
            'total' => $pendingTasks + $unreadAlerts,
        ]);
    }
}