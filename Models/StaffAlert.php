<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAlert extends Model
{
    use HasFactory;

    protected $primaryKey = 'alert_id';

    protected $fillable = [
        'sender_id',
        'sender_type',
        'recipient_id',
        'recipient_type',
        'patient_id',
        'medicine_id',
        'prescription_id',
        'appointment_id',
        'alert_type',
        'priority',
        'alert_title',
        'alert_message',
        'action_url',
        'is_read',
        'read_at',
        'is_acknowledged',
        'acknowledged_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_acknowledged' => 'boolean',
        'read_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    /**
     * The user who sent this alert
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * The user who receives this alert
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Related patient (optional)
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Related medicine (for pharmacist alerts)
     */
    public function medicine()
    {
        return $this->belongsTo(MedicineInventory::class, 'medicine_id');
    }

    /**
     * Related prescription (for pharmacist alerts)
     */
    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    /**
     * Related appointment (optional)
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    // ========================================
    // QUERY SCOPES
    // ========================================
    
    /**
     * Scope: Alerts for doctors
     */
    public function scopeForDoctor($query, $userId)
    {
        return $query->where('recipient_id', $userId)
                    ->where('recipient_type', 'doctor');
    }

    /**
     * Scope: Alerts for nurses
     */
    public function scopeForNurse($query, $userId)
    {
        return $query->where('recipient_id', $userId)
                    ->where('recipient_type', 'nurse');
    }

    /**
     * Scope: Alerts for pharmacists
     */
    public function scopeForPharmacist($query, $userId)
    {
        return $query->where('recipient_id', $userId)
                    ->where('recipient_type', 'pharmacist');
    }

    /**
     * Scope: Alerts for receptionists
     */
    public function scopeForReceptionist($query, $userId)
    {
        return $query->where('recipient_id', $userId)
                    ->where('recipient_type', 'receptionist');
    }

    /**
     * Scope: Unread alerts
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: Critical alerts
     */
    public function scopeCritical($query)
    {
        return $query->where('priority', 'Critical');
    }

    /**
     * Scope: Pending (unacknowledged) alerts
     */
    public function scopePending($query)
    {
        return $query->where('is_acknowledged', false);
    }

    /**
     * Scope: Today's alerts
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: By priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: By alert type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    // ========================================
    // HELPER METHODS
    // ========================================
    
    /**
     * Mark alert as read
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
        return $this;
    }

    /**
     * Acknowledge alert
     */
    public function acknowledge()
    {
        if (!$this->is_acknowledged) {
            $this->update([
                'is_acknowledged' => true,
                'acknowledged_at' => now(),
                'is_read' => true,
                'read_at' => $this->read_at ?? now(),
            ]);
        }
        return $this;
    }

    /**
     * Check if alert is overdue (not acknowledged within expected time)
     */
    public function isOverdue($minutesThreshold = 60)
    {
        if ($this->is_acknowledged) {
            return false;
        }

        return $this->created_at->diffInMinutes(now()) > $minutesThreshold;
    }

    /**
     * Get priority badge CSS class
     */
    public function getPriorityBadgeClass()
    {
        return match ($this->priority) {
            'Critical' => 'badge-danger priority-critical',
            'Urgent' => 'badge-warning priority-urgent',
            'High' => 'badge-info priority-high',
            'Normal' => 'badge-secondary priority-normal',
            default => 'badge-light',
        };
    }

    /**
     * Get priority icon
     */
    public function getPriorityIcon()
    {
        return match ($this->priority) {
            'Critical' => '🚨',
            'Urgent' => '⚡',
            'High' => '⚠️',
            'Normal' => 'ℹ️',
            default => '📋',
        };
    }

    /**
     * Get sender type display name
     */
    public function getSenderTypeDisplayAttribute()
    {
        return match ($this->sender_type) {
            'doctor' => '👨‍⚕️ Doctor',
            'nurse' => '👩‍⚕️ Nurse',
            'pharmacist' => '💊 Pharmacist',
            'receptionist' => '📋 Receptionist',
            'admin' => '🏥 Admin',
            'system' => '⚙️ System',
            default => ucfirst($this->sender_type),
        };
    }

    /**
     * Get recipient type display name
     */
    public function getRecipientTypeDisplayAttribute()
    {
        return match ($this->recipient_type) {
            'doctor' => '👨‍⚕️ Doctor',
            'nurse' => '👩‍⚕️ Nurse',
            'pharmacist' => '💊 Pharmacist',
            'receptionist' => '📋 Receptionist',
            'admin' => '🏥 Admin',
            default => ucfirst($this->recipient_type),
        };
    }

    /**
     * Check if this is a critical alert that requires immediate attention
     */
    public function isCritical()
    {
        return $this->priority === 'Critical';
    }

    /**
     * Check if alert requires action
     */
    public function requiresAction()
    {
        return !empty($this->action_url);
    }

    // ========================================
    // STATIC HELPERS
    // ========================================
    
    /**
     * Get unread count for specific user and role
     */
    public static function getUnreadCount($userId, $recipientType)
    {
        return self::where('recipient_id', $userId)
            ->where('recipient_type', $recipientType)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get critical unread count for specific user and role
     */
    public static function getCriticalCount($userId, $recipientType)
    {
        return self::where('recipient_id', $userId)
            ->where('recipient_type', $recipientType)
            ->where('priority', 'Critical')
            ->where('is_acknowledged', false)
            ->count();
    }

    /**
     * Mark all as read for specific user and role
     */
    public static function markAllReadForUser($userId, $recipientType)
    {
        return self::where('recipient_id', $userId)
            ->where('recipient_type', $recipientType)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Create a new alert (static helper)
     */
    public static function createAlert($data)
    {
        return self::create([
            'sender_id' => $data['sender_id'],
            'sender_type' => $data['sender_type'],
            'recipient_id' => $data['recipient_id'],
            'recipient_type' => $data['recipient_type'],
            'patient_id' => $data['patient_id'] ?? null,
            'medicine_id' => $data['medicine_id'] ?? null,
            'prescription_id' => $data['prescription_id'] ?? null,
            'appointment_id' => $data['appointment_id'] ?? null,
            'alert_type' => $data['alert_type'],
            'priority' => $data['priority'] ?? 'Normal',
            'alert_title' => $data['alert_title'],
            'alert_message' => $data['alert_message'],
            'action_url' => $data['action_url'] ?? null,
        ]);
    }

    // ========================================
    // QUERY ORDERING
    // ========================================
    
    /**
     * Scope: Order by priority
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderByRaw("
            CASE priority
                WHEN 'Critical' THEN 1
                WHEN 'Urgent' THEN 2
                WHEN 'High' THEN 3
                WHEN 'Normal' THEN 4
            END
        ");
    }

    /**
     * Scope: Default ordering (priority + unread + latest)
     */
    public function scopeDefaultOrder($query)
    {
        return $query->orderByRaw("
                CASE priority
                    WHEN 'Critical' THEN 1
                    WHEN 'Urgent' THEN 2
                    WHEN 'High' THEN 3
                    WHEN 'Normal' THEN 4
                END
            ")
            ->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc');
    }
}