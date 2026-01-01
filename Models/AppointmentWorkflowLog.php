<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentWorkflowLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'log_id';

    protected $fillable = [
        'appointment_id',
        'from_stage',
        'to_stage',
        'changed_by_id',
        'changed_by_type',
        'notes',
        'timestamp',
        'ip_address',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    // Disable Laravel's automatic timestamp management since we use 'timestamp'
    public $timestamps = false;

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * The appointment this log belongs to
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    /**
     * The user who made the change
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get formatted stage display names
     */
    public function getFromStageDisplayAttribute()
    {
        return $this->formatStageDisplay($this->from_stage);
    }

    public function getToStageDisplayAttribute()
    {
        return $this->formatStageDisplay($this->to_stage);
    }

    /**
     * Format stage names for display
     */
    protected function formatStageDisplay($stage)
    {
        return match ($stage) {
            'confirmed' => '📅 Confirmed',
            'checked_in' => '✅ Checked In',
            'vitals_pending' => '📋 Vitals Recording',
            'vitals_recorded' => '✅ Vitals Recorded',
            'ready_for_doctor' => '👨‍⚕️ Ready for Doctor',
            'in_consultation' => '🩺 In Consultation',
            'completed' => '✔️ Completed',
            'cancelled' => '❌ Cancelled',
            'no_show' => '⚠️ No Show',
            default => ucfirst(str_replace('_', ' ', $stage)),
        };
    }

    /**
     * Get user type badge color
     */
    public function getUserTypeBadgeClass()
    {
        return match ($this->changed_by_type) {
            'user', 'receptionist' => 'badge-info',
            'nurse' => 'badge-success',
            'doctor' => 'badge-primary',
            'system' => 'badge-secondary',
            default => 'badge-light',
        };
    }

    /**
     * Check if this is a critical transition
     */
    public function isCriticalTransition()
    {
        return str_contains(strtolower($this->notes ?? ''), 'critical') ||
               str_contains(strtolower($this->notes ?? ''), 'urgent');
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope: Get logs for specific appointment
     */
    public function scopeForAppointment($query, $appointmentId)
    {
        return $query->where('appointment_id', $appointmentId)
                    ->orderBy('timestamp', 'asc');
    }

    /**
     * Scope: Get logs by user type
     */
    public function scopeByUserType($query, $userType)
    {
        return $query->where('changed_by_type', $userType);
    }

    /**
     * Scope: Get logs within date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('timestamp', [$startDate, $endDate]);
    }

    /**
     * Scope: Get critical logs
     */
    public function scopeCritical($query)
    {
        return $query->where(function ($q) {
            $q->where('notes', 'like', '%critical%')
              ->orWhere('notes', 'like', '%urgent%');
        });
    }

    // ========================================
    // STATIC HELPERS
    // ========================================

    /**
     * Get workflow timeline for an appointment
     */
    public static function getTimeline($appointmentId)
    {
        return self::with('changedBy')
            ->where('appointment_id', $appointmentId)
            ->orderBy('timestamp', 'asc')
            ->get()
            ->map(function ($log) {
                return [
                    'log_id' => $log->log_id,
                    'from_stage' => $log->from_stage_display,
                    'to_stage' => $log->to_stage_display,
                    'changed_by' => $log->changedBy->name,
                    'changed_by_type' => $log->changed_by_type,
                    'notes' => $log->notes,
                    'timestamp' => $log->timestamp,
                    'time_display' => $log->timestamp->format('M d, Y h:i A'),
                    'time_ago' => $log->timestamp->diffForHumans(),
                    'is_critical' => $log->isCriticalTransition(),
                ];
            });
    }

    /**
     * Get activity summary for a date range
     */
    public static function getActivitySummary($startDate, $endDate, $groupBy = 'changed_by_type')
    {
        return self::whereBetween('timestamp', [$startDate, $endDate])
            ->select($groupBy, \DB::raw('count(*) as total'))
            ->groupBy($groupBy)
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Log a workflow change (static helper)
     */
    public static function logChange($appointmentId, $fromStage, $toStage, $userId, $userType, $notes = null)
    {
        return self::create([
            'appointment_id' => $appointmentId,
            'from_stage' => $fromStage,
            'to_stage' => $toStage,
            'changed_by_id' => $userId,
            'changed_by_type' => $userType,
            'notes' => $notes,
            'timestamp' => now(),
            'ip_address' => request()->ip(),
        ]);
    }
}