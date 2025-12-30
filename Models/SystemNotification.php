<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    use HasFactory;

    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'user_id',
        'user_role',
        'notification_type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
        'is_actionable',
        'action_url',
        'action_completed',
        'priority',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'is_actionable' => 'boolean',
        'action_completed' => 'boolean',
        'read_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Mark action as completed
     */
    public function markActionCompleted()
    {
        if ($this->is_actionable && !$this->action_completed) {
            $this->update([
                'action_completed' => true,
            ]);
        }
    }

    /**
     * Get priority badge class
     */
    public function getPriorityBadgeClass()
    {
        return match ($this->priority) {
            'urgent' => 'badge-danger',
            'high' => 'badge-warning',
            'normal' => 'badge-info',
            'low' => 'badge-secondary',
            default => 'badge-light',
        };
    }

    /**
     * Get icon based on notification type
     */
    public function getIconAttribute()
    {
        return match ($this->notification_type) {
            'patient_ready' => '👨‍⚕️',
            'critical_vitals' => '🚨',
            'appointment_reminder' => '📅',
            'new_message' => '💬',
            'prescription_ready' => '💊',
            'lab_results' => '🔬',
            'handoff' => '🔄',
            'system' => '⚙️',
            default => '🔔',
        };
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope: Unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: By priority
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Actionable notifications
     */
    public function scopeActionable($query)
    {
        return $query->where('is_actionable', true)
                    ->where('action_completed', false);
    }

    /**
     * Scope: For specific role
     */
    public function scopeForRole($query, $role)
    {
        return $query->where('user_role', $role);
    }

    /**
     * Scope: By type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    /**
     * Scope: Today's notifications
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: Recent notifications (last 24 hours)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDay());
    }

    // ========================================
    // STATIC HELPERS
    // ========================================

    /**
     * Get unread count for user
     */
    public static function getUnreadCount($userId)
    {
        return self::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Create notification for user
     */
    public static function notify($userId, $userRole, $type, $title, $message, $priority = 'normal', $data = null, $actionUrl = null)
    {
        return self::create([
            'user_id' => $userId,
            'user_role' => $userRole,
            'notification_type' => $type,
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'data' => $data,
            'is_actionable' => !is_null($actionUrl),
            'action_url' => $actionUrl,
        ]);
    }

    /**
     * Mark all as read for user
     */
    public static function markAllReadForUser($userId)
    {
        return self::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}