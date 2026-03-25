<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffTask extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'task_id';

    protected $fillable = [
        'assigned_by_id',
        'assigned_by_type',
        'assigned_to_id',
        'assigned_to_type',
        'patient_id',
        'appointment_id',
        'prescription_id',
        'medicine_id',
        'task_type',
        'priority',
        'task_title',
        'task_description',
        'action_url',
        'due_at',
        'status',
        'started_at',
        'completed_at',
        'cancelled_at',
        'completion_notes',
        'task_data',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'task_data' => 'array',
    ];

    protected $appends = ['time_remaining'];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function medicine()
    {
        return $this->belongsTo(MedicineInventory::class, 'medicine_id');
    }

    // ========================================
    // ACCESSORS
    // ========================================

    public function getTimeRemainingAttribute()
    {
        if (!$this->due_at) {
            return null;
        }

        if ($this->due_at->isPast()) {
            return 'Overdue by ' . $this->due_at->diffForHumans(null, true);
        }

        return 'Due in ' . $this->due_at->diffForHumans(null, true);
    }

    // ========================================
    // STATUS HELPERS
    // ========================================

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isOverdue()
    {
        return $this->due_at && $this->due_at->isPast() && !$this->isCompleted();
    }

    public function isActive()
    {
        return in_array($this->status, ['pending', 'in_progress']);
    }

    // ========================================
    // ACTIONS
    // ========================================

    public function start()
    {
        if ($this->status !== 'pending') {
            throw new \Exception('Task must be pending to start');
        }

        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function complete($notes = null, $data = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_notes' => $notes,
            'task_data' => $data,
        ]);

        // Send completion notification to assigner
        $this->sendCompletionNotification();
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'completion_notes' => $reason,
        ]);
    }

    protected function sendCompletionNotification()
    {
        // Create notification for the person who assigned the task
        \App\Models\SystemNotification::create([
            'user_id' => $this->assigned_by_id,
            'user_role' => $this->assigned_by_type,
            'notification_type' => 'task_completed',
            'title' => 'Task Completed',
            'message' => "{$this->assignedTo->name} completed: {$this->task_title}",
            'data' => [
                'task_id' => $this->task_id,
                'completed_by' => $this->assignedTo->name,
                'completion_notes' => $this->completion_notes,
            ],
            'is_actionable' => true,
            'action_url' => route("{$this->assigned_by_type}.tasks.show", $this->task_id),
            'priority' => 'normal',
        ]);
    }

    // ========================================
    // DISPLAY HELPERS
    // ========================================

    public function getPriorityBadgeClass()
    {
        return match ($this->priority) {
            'Critical' => 'badge-danger',
            'Urgent' => 'badge-warning',
            'High' => 'badge-info',
            'Normal' => 'badge-secondary',
            'Low' => 'badge-light',
            default => 'badge-secondary',
        };
    }

    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            'pending' => 'badge-warning',
            'in_progress' => 'badge-info',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope: Tasks for specific staff member
     */
    public function scopeForStaff($query, $userId, $userType)
    {
        return $query->where('assigned_to_id', $userId)
                    ->where('assigned_to_type', $userType);
    }

    /**
     * Scope: Pending tasks
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: In progress tasks
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope: Completed tasks
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Active tasks (pending or in progress)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }

    /**
     * Scope: Overdue tasks
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_at', '<', now())
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope: Urgent tasks (Urgent or Critical priority)
     */
    public function scopeUrgent($query)
    {
        return $query->whereIn('priority', ['Urgent', 'Critical']);
    }

    /**
     * Scope: Due today
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('due_at', today())
                    ->active();
    }

    /**
     * Scope: By priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Critical priority
     */
    public function scopeCritical($query)
    {
        return $query->where('priority', 'Critical');
    }

    /**
     * Scope: Order by priority (Critical first)
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderByRaw("
            CASE priority
                WHEN 'Critical' THEN 1
                WHEN 'Urgent' THEN 2
                WHEN 'High' THEN 3
                WHEN 'Normal' THEN 4
                WHEN 'Low' THEN 5
            END
        ");
    }

    /**
     * Scope: Order by status (in_progress first)
     */
    public function scopeOrderByStatus($query)
    {
        return $query->orderByRaw("
            CASE status
                WHEN 'in_progress' THEN 1
                WHEN 'pending' THEN 2
                WHEN 'completed' THEN 3
                WHEN 'cancelled' THEN 4
            END
        ");
    }
}