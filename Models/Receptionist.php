<?php
// app/Models/Receptionist.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receptionist extends Model
{
    use HasFactory;

    protected $primaryKey = 'receptionist_id';

    protected $fillable = [
        'user_id',
        'phone_number',
        'profile_photo',
        'employee_id',
        'department',
        'shift',
        'hire_date',
        'availability_status',
        'patients_checked_in_today',
        'total_patients_checked_in',
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function checkedInAppointments()
    {
        return $this->hasMany(Appointment::class, 'checked_in_by', 'user_id');
    }

    public function todayCheckIns()
    {
        return $this->checkedInAppointments()
            ->whereDate('arrived_at', today());
    }

    public function alerts()
    {
        return $this->hasMany(StaffAlert::class, 'recipient_id')
            ->where('recipient_type', 'receptionist');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get full name
     */
    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    /**
     * Check if receptionist is available
     */
    public function isAvailable()
    {
        return $this->availability_status === 'Available';
    }

    /**
     * Increment today's check-in count
     */
    public function incrementCheckInCount()
    {
        $this->increment('patients_checked_in_today');
        $this->increment('total_patients_checked_in');
    }

    /**
     * Reset daily check-in count (run this daily via scheduler)
     */
    public static function resetDailyCheckInCounts()
    {
        self::query()->update(['patients_checked_in_today' => 0]);
    }

    /**
     * Get unread alert count
     */
    public function getUnreadAlertsCount()
    {
        return $this->alerts()->unread()->count();
    }

    /**
     * Get critical alerts
     */
    public function getCriticalAlerts()
    {
        return $this->alerts()
            ->critical()
            ->pending()
            ->defaultOrder()
            ->get();
    }

    /**
     * Get today's check-in count
     */
    public function getTodayCheckInCount()
    {
        return $this->todayCheckIns()->count();
    }

    /**
     * Get today's check-in performance
     */
    public function getTodayPerformance()
    {
        $totalToday = $this->todayCheckIns()->count();
        $onTime = $this->todayCheckIns()
            ->where('is_late', false)
            ->count();
        
        return [
            'total_checked_in' => $totalToday,
            'on_time' => $onTime,
            'late' => $totalToday - $onTime,
            'on_time_percentage' => $totalToday > 0 ? ($onTime / $totalToday) * 100 : 0,
        ];
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope: Available receptionists
     */
    public function scopeAvailable($query)
    {
        return $query->where('availability_status', 'Available');
    }

    /**
     * Scope: By shift
     */
    public function scopeByShift($query, $shift)
    {
        return $query->where('shift', $shift);
    }

    /**
     * Scope: On duty today
     */
    public function scopeOnDutyToday($query)
    {
        // This would be enhanced with shift schedule integration
        return $query->where('availability_status', 'Available');
    }
}