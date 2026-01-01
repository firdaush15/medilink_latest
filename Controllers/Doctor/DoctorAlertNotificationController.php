<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\StaffAlert;
use App\Models\StaffTask;
use App\Models\User;
use App\Models\Patient;
use Carbon\Carbon;

class DoctorAlertNotificationController extends Controller
{
    /**
     * Display received alerts (Inbox)
     */
    public function inbox(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        if (!$doctor) {
            return redirect()->route('doctor.dashboard')->with('error', 'Doctor profile not found');
        }

        $filter = $request->get('filter', 'all');
        $search = $request->get('search');

        $alertsQuery = StaffAlert::with(['sender', 'patient.user'])
            ->where('recipient_id', auth()->id())
            ->where('recipient_type', 'doctor');

        $this->applyFilters($alertsQuery, $filter);

        if ($search) {
            $alertsQuery->where(function($q) use ($search) {
                $q->where('alert_title', 'like', "%{$search}%")
                  ->orWhere('alert_message', 'like', "%{$search}%")
                  ->orWhereHas('sender', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $alerts = $alertsQuery->orderByRaw("
                CASE priority
                    WHEN 'Critical' THEN 1
                    WHEN 'Urgent' THEN 2
                    WHEN 'High' THEN 3
                    WHEN 'Normal' THEN 4
                END
            ")
            ->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $counts = [
            'total' => StaffAlert::where('recipient_id', auth()->id())->where('recipient_type', 'doctor')->count(),
            'unread' => StaffAlert::where('recipient_id', auth()->id())->where('recipient_type', 'doctor')->where('is_read', false)->count(),
            'critical' => StaffAlert::where('recipient_id', auth()->id())->where('recipient_type', 'doctor')->where('priority', 'Critical')->where('is_acknowledged', false)->count(),
            'pending' => StaffAlert::where('recipient_id', auth()->id())->where('recipient_type', 'doctor')->where('is_acknowledged', false)->count(),
            'today' => StaffAlert::where('recipient_id', auth()->id())->where('recipient_type', 'doctor')->whereDate('created_at', today())->count(),
        ];

        return view('doctor.doctor_alertInbox', compact('alerts', 'filter', 'counts', 'doctor'));
    }

    /**
     * Display sent alerts and tasks (Outbox)
     */
    public function outbox(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        if (!$doctor) {
            return redirect()->route('doctor.dashboard')->with('error', 'Doctor profile not found');
        }

        $view = $request->get('view', 'all');

        // Get sent alerts
        $sentAlerts = StaffAlert::with(['recipient', 'patient.user'])
            ->where('sender_id', auth()->id())
            ->where('sender_type', 'doctor')
            ->get()
            ->map(function($alert) {
                return [
                    'id' => $alert->alert_id,
                    'type' => 'alert',
                    'title' => $alert->alert_title,
                    'message' => $alert->alert_message,
                    'priority' => $alert->priority,
                    'recipient_name' => $alert->recipient->name,
                    'recipient_type' => $alert->recipient_type,
                    'patient_name' => $alert->patient ? $alert->patient->user->name : null,
                    'created_at' => $alert->created_at->format('M d, Y g:i A'),
                    'is_read' => $alert->is_read,
                    'is_acknowledged' => $alert->is_acknowledged,
                    'status' => null,
                    'due_at' => null,
                ];
            });

        // Get assigned tasks
        $assignedTasks = StaffTask::with(['assignedTo', 'patient.user'])
            ->where('assigned_by_id', auth()->id())
            ->where('assigned_by_type', 'doctor')
            ->get()
            ->map(function($task) {
                return [
                    'id' => $task->task_id,
                    'type' => 'task',
                    'title' => $task->task_title,
                    'message' => $task->task_description ?? 'No description provided',
                    'priority' => $task->priority,
                    'recipient_name' => $task->assignedTo->name,
                    'recipient_type' => $task->assigned_to_type,
                    'patient_name' => $task->patient ? $task->patient->user->name : null,
                    'created_at' => $task->created_at->format('M d, Y g:i A'),
                    'is_read' => null,
                    'is_acknowledged' => null,
                    'status' => $task->status,
                    'due_at' => $task->due_at ? $task->due_at->format('M d, g:i A') : null,
                ];
            });

        // Combine and filter
        $items = collect();
        
        if ($view == 'all' || $view == 'alerts') {
            $items = $items->merge($sentAlerts);
        }
        
        if ($view == 'all' || $view == 'tasks') {
            $items = $items->merge($assignedTasks);
        }

        // Sort by created_at
        $items = $items->sortByDesc(function($item) {
            return Carbon::parse($item['created_at']);
        });

        // Paginate manually
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $perPage = 15;
        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($currentPage, $perPage),
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        // Get staff lists
        $nurses = User::where('role', 'nurse')->get();
        $pharmacists = User::where('role', 'pharmacist')->get();
        $receptionists = User::where('role', 'receptionist')->get();

        // Get patients
        $patients = Patient::whereHas('appointments', function($q) use ($doctor) {
            $q->where('doctor_id', $doctor->doctor_id)
              ->where('appointment_date', '>=', now()->subMonths(6));
        })->with('user')->get();

        // Statistics
        $stats = [
            'total_alerts' => StaffAlert::where('sender_id', auth()->id())->count(),
            'total_tasks' => StaffTask::where('assigned_by_id', auth()->id())->where('assigned_by_type', 'doctor')->count(),
            'pending_tasks' => StaffTask::where('assigned_by_id', auth()->id())->where('assigned_by_type', 'doctor')->whereIn('status', ['pending', 'in_progress'])->count(),
            'completed_today' => StaffTask::where('assigned_by_id', auth()->id())->where('assigned_by_type', 'doctor')->where('status', 'completed')->whereDate('completed_at', today())->count(),
        ];

        return view('doctor.doctor_alertOutbox', compact('items', 'nurses', 'pharmacists', 'receptionists', 'patients', 'stats', 'view', 'doctor'));
    }

    /**
     * Assign a task
     */
    public function assignTask(Request $request)
    {
        $validated = $request->validate([
            'assigned_to_id' => 'required|exists:users,id',
            'assigned_to_type' => 'required|in:nurse,pharmacist,receptionist',
            'patient_id' => 'required|exists:patients,patient_id',
            'task_type' => 'required|string',
            'priority' => 'required|in:Critical,Urgent,High,Normal,Low',
            'task_title' => 'required|string|max:255',
            'task_description' => 'nullable|string|max:1000',
            'due_at' => 'nullable|date',
        ]);

        try {
            $task = StaffTask::create([
                'assigned_by_id' => auth()->id(),
                'assigned_by_type' => 'doctor',
                'assigned_to_id' => $validated['assigned_to_id'],
                'assigned_to_type' => $validated['assigned_to_type'],
                'patient_id' => $validated['patient_id'],
                'task_type' => $validated['task_type'],
                'priority' => $validated['priority'],
                'task_title' => $validated['task_title'],
                'task_description' => $validated['task_description'] ?? null,
                'due_at' => $validated['due_at'] ?? null,
                'status' => 'pending',
            ]);

            // Create notification alert
            StaffAlert::create([
                'sender_id' => auth()->id(),
                'sender_type' => 'doctor',
                'recipient_id' => $validated['assigned_to_id'],
                'recipient_type' => $validated['assigned_to_type'],
                'patient_id' => $validated['patient_id'],
                'alert_type' => 'Task Assigned',
                'priority' => $validated['priority'],
                'alert_title' => '📋 New Task: ' . $validated['task_title'],
                'alert_message' => 'You have been assigned a new task by Dr. ' . auth()->user()->name,
                'action_url' => route($validated['assigned_to_type'] . '.tasks'),
            ]);

            return redirect()->back()->with('success', 'Task assigned successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error assigning task: ' . $e->getMessage());
        }
    }

    /**
     * Quick assign task with template
     */
    public function quickAssignTask(Request $request)
    {
        $validated = $request->validate([
            'task_type' => 'required|string',
            'assigned_to_id' => 'required|exists:users,id',
            'assigned_to_type' => 'required|string',
            'patient_id' => 'required|exists:patients,patient_id',
            'priority' => 'required|in:Critical,Urgent,High,Normal,Low',
        ]);

        $patient = Patient::with('user')->find($validated['patient_id']);
        $template = $this->getTaskTemplate($validated['task_type'], $patient);

        try {
            $task = StaffTask::create([
                'assigned_by_id' => auth()->id(),
                'assigned_by_type' => 'doctor',
                'assigned_to_id' => $validated['assigned_to_id'],
                'assigned_to_type' => $validated['assigned_to_type'],
                'patient_id' => $validated['patient_id'],
                'task_type' => $validated['task_type'],
                'priority' => $validated['priority'],
                'task_title' => $template['title'],
                'task_description' => $template['description'],
                'status' => 'pending',
            ]);

            StaffAlert::create([
                'sender_id' => auth()->id(),
                'sender_type' => 'doctor',
                'recipient_id' => $validated['assigned_to_id'],
                'recipient_type' => $validated['assigned_to_type'],
                'patient_id' => $validated['patient_id'],
                'alert_type' => 'Task Assigned',
                'priority' => $validated['priority'],
                'alert_title' => '📋 New Task: ' . $template['title'],
                'alert_message' => 'Quick task assigned by Dr. ' . auth()->user()->name,
                'action_url' => route($validated['assigned_to_type'] . '.tasks'),
            ]);

            return redirect()->back()->with('success', 'Task assigned successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error assigning task');
        }
    }

    /**
     * Send a new alert
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'recipient_type' => 'required|in:nurse,pharmacist,receptionist',
            'patient_id' => 'nullable|exists:patients,patient_id',
            'alert_type' => 'required|string',
            'priority' => 'required|in:Critical,Urgent,High,Normal',
            'alert_title' => 'required|string|max:255',
            'alert_message' => 'required|string|max:1000',
        ]);

        try {
            StaffAlert::create([
                'sender_id' => auth()->id(),
                'sender_type' => 'doctor',
                'recipient_id' => $validated['recipient_id'],
                'recipient_type' => $validated['recipient_type'],
                'patient_id' => $validated['patient_id'] ?? null,
                'alert_type' => $validated['alert_type'],
                'priority' => $validated['priority'],
                'alert_title' => $validated['alert_title'],
                'alert_message' => $validated['alert_message'],
            ]);

            return redirect()->back()->with('success', 'Alert sent successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error sending alert');
        }
    }

    public function markAsRead($id)
    {
        $alert = StaffAlert::findOrFail($id);
        if ($alert->recipient_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $alert->update(['is_read' => true, 'read_at' => now()]);
        return redirect()->back()->with('success', 'Alert marked as read');
    }

    public function acknowledge($id)
    {
        $alert = StaffAlert::findOrFail($id);
        if ($alert->recipient_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $alert->update([
            'is_acknowledged' => true,
            'acknowledged_at' => now(),
            'is_read' => true,
            'read_at' => $alert->read_at ?? now()
        ]);
        return redirect()->back()->with('success', 'Alert acknowledged');
    }

    public function markAllRead()
    {
        StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'doctor')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        return redirect()->back()->with('success', 'All alerts marked as read');
    }

    public function destroy($id)
    {
        $alert = StaffAlert::findOrFail($id);
        if ($alert->sender_id !== auth()->id()) {
            return response()->json(['success' => false], 403);
        }
        $alert->delete();
        return redirect()->back()->with('success', 'Alert deleted');
    }

    public function destroyTask($id)
    {
        $task = StaffTask::findOrFail($id);
        if ($task->assigned_by_id !== auth()->id()) {
            return response()->json(['success' => false], 403);
        }
        $task->delete();
        return redirect()->back()->with('success', 'Task deleted');
    }

    private function applyFilters($query, $filter)
    {
        switch ($filter) {
            case 'unread': $query->where('is_read', false); break;
            case 'critical': $query->where('priority', 'Critical')->where('is_acknowledged', false); break;
            case 'pending': $query->where('is_acknowledged', false); break;
            case 'acknowledged': $query->where('is_acknowledged', true); break;
            case 'today': $query->whereDate('created_at', today()); break;
        }
    }

    private function getTaskTemplate($taskType, $patient)
    {
        $name = $patient->user->name;
        
        $templates = [
            'Vital Signs Check' => [
                'title' => "Check Vital Signs - {$name}",
                'description' => "Please check and record vital signs (BP, Temperature, Pulse, SpO2) for {$name}.",
            ],
            'Prepare Patient' => [
                'title' => "Prepare Patient - {$name}",
                'description' => "Prepare {$name} for upcoming procedure or examination.",
            ],
            'Collect Specimen' => [
                'title' => "Collect Specimen - {$name}",
                'description' => "Collect lab specimens from {$name} as per doctor's orders.",
            ],
            'Medication Reminder' => [
                'title' => "Medication Reminder - {$name}",
                'description' => "Remind {$name} to take prescribed medications.",
            ],
        ];

        return $templates[$taskType] ?? [
            'title' => "Task for {$name}",
            'description' => "Please attend to {$name}.",
        ];
    }
}