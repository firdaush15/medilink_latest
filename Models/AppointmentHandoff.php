<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentHandoff extends Model
{
    use HasFactory;

    protected $primaryKey = 'handoff_id';

    protected $fillable = [
        'appointment_id',
        'handoff_type',
        'from_user_id',
        'to_user_id',
        'initiated_at',
        'acknowledged_at',
        'is_acknowledged',
        'handoff_notes',
        'requires_immediate_attention',
        'critical_information',
    ];

    protected $casts = [
        'initiated_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'is_acknowledged' => 'boolean',
        'requires_immediate_attention' => 'boolean',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Acknowledge the handoff
     */
    public function acknowledge()
    {
        if (!$this->is_acknowledged) {
            $this->update([
                'is_acknowledged' => true,
                'acknowledged_at' => now(),
            ]);
        }
    }

    /**
     * Check if handoff is overdue (not acknowledged within expected time)
     */
    public function isOverdue($minutesThreshold = 30)
    {
        if ($this->is_acknowledged) {
            return false;
        }

        return $this->initiated_at->diffInMinutes(now()) > $minutesThreshold;
    }

    /**
     * Get handoff type display name
     */
    public function getHandoffTypeDisplayAttribute()
    {
        return match ($this->handoff_type) {
            'receptionist_to_nurse' => '👨‍💼 → 👩‍⚕️ Receptionist to Nurse',
            'nurse_to_doctor' => '👩‍⚕️ → 👨‍⚕️ Nurse to Doctor',
            'doctor_to_nurse' => '👨‍⚕️ → 👩‍⚕️ Doctor to Nurse',
            'nurse_to_receptionist' => '👩‍⚕️ → 👨‍💼 Nurse to Receptionist',
            default => ucfirst(str_replace('_', ' ', $this->handoff_type)),
        };
    }

    /**
     * Get priority badge class
     */
    public function getPriorityBadgeClass()
    {
        return $this->requires_immediate_attention ? 'badge-danger' : 'badge-info';
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope: Pending handoffs (not acknowledged)
     */
    public function scopePending($query)
    {
        return $query->where('is_acknowledged', false);
    }

    /**
     * Scope: Handoffs for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('to_user_id', $userId);
    }

    /**
     * Scope: Critical/urgent handoffs
     */
    public function scopeCritical($query)
    {
        return $query->where('requires_immediate_attention', true);
    }

    /**
     * Scope: By handoff type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('handoff_type', $type);
    }

    /**
     * Scope: Today's handoffs
     */
    public function scopeToday($query)
    {
        return $query->whereDate('initiated_at', today());
    }
}