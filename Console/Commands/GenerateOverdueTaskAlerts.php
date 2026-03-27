<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StaffTask;
use App\Models\StaffAlert;

class GenerateOverdueTaskAlerts extends Command
{
    protected $signature = 'alerts:overdue-tasks';
    protected $description = 'Send alerts for overdue staff tasks';

    public function handle()
    {
        $count = 0;

        StaffTask::overdue()
            ->whereNull('overdue_notified_at')
            ->with(['assignedTo', 'assignedBy', 'patient'])
            ->each(function ($task) use (&$count) {

                // Alert the assigned staff member (reminder to complete)
                StaffAlert::create([
                    'sender_id'      => $task->assigned_by_id,
                    'sender_type'    => $task->assigned_by_type,
                    'recipient_id'   => $task->assigned_to_id,
                    'recipient_type' => $task->assigned_to_type,
                    'patient_id'     => $task->patient_id,
                    'alert_type'     => 'Overdue Task',
                    'priority'       => 'Urgent',
                    'alert_title'    => 'Overdue: ' . $task->task_title,
                    'alert_message'  => "This task was due {$task->due_at->diffForHumans()} and has not been completed. "
                                      . "Please action it immediately.",
                    'action_url'     => route($task->assigned_to_type . '.tasks.show', $task->task_id),
                ]);

                // Also notify the person who assigned the task
                StaffAlert::create([
                    'sender_id'      => $task->assigned_to_id,
                    'sender_type'    => $task->assigned_to_type,
                    'recipient_id'   => $task->assigned_by_id,
                    'recipient_type' => $task->assigned_by_type,
                    'patient_id'     => $task->patient_id,
                    'alert_type'     => 'Overdue Task',
                    'priority'       => 'High',
                    'alert_title'    => 'Task Overdue: ' . $task->task_title,
                    'alert_message'  => "A task you assigned to {$task->assignedTo->name} is overdue "
                                      . "({$task->due_at->diffForHumans()}) and has not been completed.",
                    'action_url'     => route('doctor.alerts.outbox'),
                ]);

                // Mark as notified so we don't spam
                $task->update(['overdue_notified_at' => now()]);

                $count++;
            });

        $this->info("Overdue task alerts sent: {$count}");

        return Command::SUCCESS;
    }
}