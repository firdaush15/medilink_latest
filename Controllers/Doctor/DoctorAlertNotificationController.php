<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\StaffAlert;
use App\Models\StaffTask;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use Carbon\Carbon;

class DoctorAlertNotificationController extends Controller
{
    /**
     * Display received alerts (Inbox).
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
            $alertsQuery->where(function ($q) use ($search) {
                $q->where('alert_title', 'like', "%{$search}%")
                  ->orWhere('alert_message', 'like', "%{$search}%")
                  ->orWhereHas('sender', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $alerts = $alertsQuery
            ->orderByRaw("
                CASE priority
                    WHEN 'Critical' THEN 1
                    WHEN 'Urgent'   THEN 2
                    WHEN 'High'     THEN 3
                    WHEN 'Normal'   THEN 4
                END
            ")
            ->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $rawCounts = StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'doctor')
            ->selectRaw("
                COUNT(*) as total,
                SUM(is_read = 0) as unread,
                SUM(priority = 'Critical' AND is_acknowledged = 0) as critical,
                SUM(is_acknowledged = 0) as pending,
                SUM(DATE(created_at) = CURDATE()) as today
            ")
            ->first();

        $systemUnread = SystemNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        $counts = [
            'total'    => $rawCounts->total    ?? 0,
            'unread'   => ($rawCounts->unread  ?? 0) + $systemUnread,
            'critical' => $rawCounts->critical ?? 0,
            'pending'  => $rawCounts->pending  ?? 0,
            'today'    => $rawCounts->today    ?? 0,
        ];

        return view('doctor.doctor_alertInbox', compact('alerts', 'filter', 'counts', 'doctor'));
    }

    /**
     * Soft-poll endpoint for navbar badge.
     */
    public function getUnreadCount()
    {
        $alertUnread = StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'doctor')
            ->where('is_read', false)
            ->count();

        $systemUnread = SystemNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        $critical = StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'doctor')
            ->where('priority', 'Critical')
            ->where('is_acknowledged', false)
            ->count();

        return response()->json([
            'count'    => $alertUnread + $systemUnread,
            'critical' => $critical,
        ]);
    }

    /**
     * Display sent alerts (Outbox) — alerts only, no tasks.
     */
    public function outbox(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        if (!$doctor) {
            return redirect()->route('doctor.dashboard')->with('error', 'Doctor profile not found');
        }

        $sentAlerts = StaffAlert::with(['recipient', 'patient.user'])
            ->where('sender_id', auth()->id())
            ->where('sender_type', 'doctor')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Staff lists for the Send Alert modal
        $nurses        = User::where('role', 'nurse')->get();
        $pharmacists   = User::where('role', 'pharmacist')->get();
        $receptionists = User::where('role', 'receptionist')->get();

        // Only active patients for the alert patient selector
        $patients = Patient::whereHas('appointments', function ($q) use ($doctor) {
            $q->where('doctor_id', $doctor->doctor_id)
              ->where(function ($inner) {
                  $inner->whereDate('appointment_date', today())
                        ->whereNotIn('status', ['completed', 'cancelled', 'no_show']);
              })
              ->orWhere(function ($inner) {
                  $inner->whereDate('appointment_date', '>', today())
                        ->where('status', 'confirmed');
              });
        })->with('user')->get();

        $stats = [
            'total_alerts' => StaffAlert::where('sender_id', auth()->id())
                ->where('sender_type', 'doctor')
                ->count(),
            'today'        => StaffAlert::where('sender_id', auth()->id())
                ->where('sender_type', 'doctor')
                ->whereDate('created_at', today())
                ->count(),
            'read'         => StaffAlert::where('sender_id', auth()->id())
                ->where('sender_type', 'doctor')
                ->where('is_read', true)
                ->count(),
            'acknowledged' => StaffAlert::where('sender_id', auth()->id())
                ->where('sender_type', 'doctor')
                ->where('is_acknowledged', true)
                ->count(),
        ];

        return view('doctor.doctor_alertOutbox', compact(
            'sentAlerts', 'nurses', 'pharmacists', 'receptionists', 'patients', 'stats', 'doctor'
        ));
    }

    /**
     * Send a new alert.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient_id'   => 'required|exists:users,id',
            'recipient_type' => 'required|in:nurse,pharmacist,receptionist',
            'patient_id'     => 'nullable|exists:patients,patient_id',
            'alert_type'     => 'required|string',
            'priority'       => 'required|in:Critical,Urgent,High,Normal',
            'alert_title'    => 'required|string|max:255',
            'alert_message'  => 'required|string|max:1000',
        ]);

        try {
            StaffAlert::create([
                'sender_id'      => auth()->id(),
                'sender_type'    => 'doctor',
                'recipient_id'   => $validated['recipient_id'],
                'recipient_type' => $validated['recipient_type'],
                'patient_id'     => $validated['patient_id'] ?? null,
                'alert_type'     => $validated['alert_type'],
                'priority'       => $validated['priority'],
                'alert_title'    => $validated['alert_title'],
                'alert_message'  => $validated['alert_message'],
            ]);

            return redirect()->back()->with('success', 'Alert sent successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error sending alert');
        }
    }

    public function markAsRead($id)
    {
        $alert = StaffAlert::findOrFail($id);

        if ($alert->recipient_id !== auth()->id() || $alert->recipient_type !== 'doctor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $alert->update(['is_read' => true, 'read_at' => now()]);

        return redirect()->back()->with('success', 'Alert marked as read');
    }

    public function acknowledge($id)
    {
        $alert = StaffAlert::findOrFail($id);

        if ($alert->recipient_id !== auth()->id() || $alert->recipient_type !== 'doctor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $alert->update([
            'is_acknowledged' => true,
            'acknowledged_at' => now(),
            'is_read'         => true,
            'read_at'         => $alert->read_at ?? now(),
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

    // ── Task methods kept for any existing routes, but outbox no longer uses them ──

    public function assignTask(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();

        $validated = $request->validate([
            'assigned_to_id'   => 'required|exists:users,id',
            'assigned_to_type' => 'required|in:nurse,pharmacist,receptionist',
            'patient_id'       => [
                'required',
                'exists:patients,patient_id',
                function ($attribute, $value, $fail) use ($doctor) {
                    $hasActive = Appointment::where('patient_id', $value)
                        ->where('doctor_id', $doctor->doctor_id)
                        ->where(function ($q) {
                            $q->where(function ($inner) {
                                $inner->whereDate('appointment_date', today())
                                      ->whereNotIn('status', ['completed', 'cancelled', 'no_show']);
                            })->orWhere(function ($inner) {
                                $inner->whereDate('appointment_date', '>', today())
                                      ->where('status', 'confirmed');
                            });
                        })
                        ->exists();

                    if (!$hasActive) {
                        $fail('This patient does not have an active appointment with you today or upcoming.');
                    }
                },
            ],
            'task_type'        => 'required|string',
            'priority'         => 'required|in:Critical,Urgent,High,Normal,Low',
            'task_title'       => 'required|string|max:255',
            'task_description' => 'nullable|string|max:1000',
            'due_at'           => 'nullable|date',
        ]);

        try {
            StaffTask::create([
                'assigned_by_id'   => auth()->id(),
                'assigned_by_type' => 'doctor',
                'assigned_to_id'   => $validated['assigned_to_id'],
                'assigned_to_type' => $validated['assigned_to_type'],
                'patient_id'       => $validated['patient_id'],
                'task_type'        => $validated['task_type'],
                'priority'         => $validated['priority'],
                'task_title'       => $validated['task_title'],
                'task_description' => $validated['task_description'] ?? null,
                'due_at'           => $validated['due_at'] ?? null,
                'status'           => 'pending',
            ]);

            StaffAlert::create([
                'sender_id'      => auth()->id(),
                'sender_type'    => 'doctor',
                'recipient_id'   => $validated['assigned_to_id'],
                'recipient_type' => $validated['assigned_to_type'],
                'patient_id'     => $validated['patient_id'],
                'alert_type'     => 'Task Assigned',
                'priority'       => $validated['priority'],
                'alert_title'    => 'New Task: ' . $validated['task_title'],
                'alert_message'  => 'You have been assigned a new task by Dr. ' . auth()->user()->name,
                'action_url'     => route($validated['assigned_to_type'] . '.tasks'),
            ]);

            return redirect()->back()->with('success', 'Task assigned successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error assigning task: ' . $e->getMessage());
        }
    }

    public function quickAssignTask(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();

        $validated = $request->validate([
            'task_type'        => 'required|string',
            'assigned_to_id'   => 'required|exists:users,id',
            'assigned_to_type' => 'required|string',
            'patient_id'       => [
                'required',
                'exists:patients,patient_id',
                function ($attribute, $value, $fail) use ($doctor) {
                    $hasActive = Appointment::where('patient_id', $value)
                        ->where('doctor_id', $doctor->doctor_id)
                        ->where(function ($q) {
                            $q->where(function ($inner) {
                                $inner->whereDate('appointment_date', today())
                                      ->whereNotIn('status', ['completed', 'cancelled', 'no_show']);
                            })->orWhere(function ($inner) {
                                $inner->whereDate('appointment_date', '>', today())
                                      ->where('status', 'confirmed');
                            });
                        })
                        ->exists();

                    if (!$hasActive) {
                        $fail('This patient does not have an active appointment with you today or upcoming.');
                    }
                },
            ],
            'priority' => 'required|in:Critical,Urgent,High,Normal,Low',
        ]);

        $patient  = Patient::with('user')->find($validated['patient_id']);
        $template = $this->getTaskTemplate($validated['task_type'], $patient);

        try {
            StaffTask::create([
                'assigned_by_id'   => auth()->id(),
                'assigned_by_type' => 'doctor',
                'assigned_to_id'   => $validated['assigned_to_id'],
                'assigned_to_type' => $validated['assigned_to_type'],
                'patient_id'       => $validated['patient_id'],
                'task_type'        => $validated['task_type'],
                'priority'         => $validated['priority'],
                'task_title'       => $template['title'],
                'task_description' => $template['description'],
                'status'           => 'pending',
            ]);

            StaffAlert::create([
                'sender_id'      => auth()->id(),
                'sender_type'    => 'doctor',
                'recipient_id'   => $validated['assigned_to_id'],
                'recipient_type' => $validated['assigned_to_type'],
                'patient_id'     => $validated['patient_id'],
                'alert_type'     => 'Task Assigned',
                'priority'       => $validated['priority'],
                'alert_title'    => 'New Task: ' . $template['title'],
                'alert_message'  => 'Quick task assigned by Dr. ' . auth()->user()->name,
                'action_url'     => route($validated['assigned_to_type'] . '.tasks'),
            ]);

            return redirect()->back()->with('success', 'Task assigned successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error assigning task');
        }
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

    // ── Private helpers ──────────────────────────────────────────────────────

    private function applyFilters($query, $filter)
    {
        switch ($filter) {
            case 'unread':       $query->where('is_read', false); break;
            case 'critical':     $query->where('priority', 'Critical')->where('is_acknowledged', false); break;
            case 'pending':      $query->where('is_acknowledged', false); break;
            case 'acknowledged': $query->where('is_acknowledged', true); break;
            case 'today':        $query->whereDate('created_at', today()); break;
        }
    }

    private function getTaskTemplate($taskType, $patient)
    {
        $name = $patient->user->name;

        $templates = [
            'Vital Signs Check' => [
                'title'       => "Check Vital Signs - {$name}",
                'description' => "Please check and record vital signs (BP, Temperature, Pulse, SpO2) for {$name}.",
            ],
            'Prepare Patient' => [
                'title'       => "Prepare Patient - {$name}",
                'description' => "Prepare {$name} for upcoming procedure or examination.",
            ],
            'Collect Specimen' => [
                'title'       => "Collect Specimen - {$name}",
                'description' => "Collect lab specimens from {$name} as per doctor's orders.",
            ],
            'Medication Reminder' => [
                'title'       => "Medication Reminder - {$name}",
                'description' => "Remind {$name} to take prescribed medications.",
            ],
            'Verify Prescription' => [
                'title'       => "Verify Prescription - {$name}",
                'description' => "Please verify and dispense the prescription for {$name}.",
            ],
            'Check Stock' => [
                'title'       => "Check Stock Availability",
                'description' => "Please check current stock levels and flag any shortages.",
            ],
        ];

        return $templates[$taskType] ?? [
            'title'       => "Task for {$name}",
            'description' => "Please attend to {$name}.",
        ];
    }
}