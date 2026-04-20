<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nurse extends Model
{
    use HasFactory;

    protected $primaryKey = 'nurse_id';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'license_number',
        'specialization',
        'phone_number',
        'email',
        'date_of_birth',
        'hire_date',
        'department',
        'shift',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedPatients()
    {
        return $this->hasMany(Patient::class, 'assigned_nurse_id', 'nurse_id');
    }

    public function vitalSigns()
    {
        return $this->hasMany(VitalSign::class, 'nurse_id');
    }

    public function alerts()
    {
        return $this->hasMany(StaffAlert::class, 'recipient_id')
                    ->where('recipient_type', 'nurse');
    }

    // Add these methods to your existing Nurse model

public function doctorAssignments()
{
    return $this->hasMany(NurseDoctorAssignment::class, 'nurse_id');
}

public function assignedDoctors()
{
    return $this->belongsToMany(Doctor::class, 'nurse_doctor_assignments', 'nurse_id', 'doctor_id')
        ->using(NurseDoctorAssignment::class)
        ->withPivot([
            'assignment_id',
            'assignment_type',
            'priority_order',
            'working_days',
            'shift_start',
            'shift_end',
            'is_active',
            'assigned_from',
            'assigned_until',
        ])
        ->withTimestamps()
        ->wherePivot('is_active', true)  // ✅ FIX: Use wherePivot instead of where
        ->orderByPivot('priority_order'); // ✅ FIX: Use orderByPivot for pivot columns
}

public function workload()
{
    return $this->hasOne(NurseWorkloadTracking::class, 'nurse_id');
}

public function currentAssignments()
{
    return $this->hasMany(PatientNurseAssignment::class, 'nurse_id')
        ->whereIn('status', ['pending', 'accepted', 'in_progress']);
}

public function patientAssignments()
{
    return $this->hasMany(PatientNurseAssignment::class, 'nurse_id');
}

    // ========================================
    // ALERT HELPER METHODS
    // ========================================

    /**
     * Get unread alert count
     */
    public function getUnreadAlertsCount()
    {
        return $this->alerts()->unread()->count();
    }

    /**
     * Get critical alerts that need immediate attention
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
     * Get all pending (unacknowledged) alerts
     */
    public function getPendingAlerts()
    {
        return $this->alerts()
                    ->pending()
                    ->defaultOrder()
                    ->get();
    }

    /**
     * Get today's alerts
     */
    public function getTodayAlerts()
    {
        return $this->alerts()
                    ->today()
                    ->defaultOrder()
                    ->get();
    }

    /**
     * Get recent alerts (last 24 hours)
     */
    public function getRecentAlerts($limit = 10)
    {
        return $this->alerts()
                    ->where('created_at', '>=', now()->subDay())
                    ->defaultOrder()
                    ->limit($limit)
                    ->get();
    }

    /**
     * Mark all alerts as read
     */
    public function markAllAlertsRead()
    {
        return StaffAlert::markAllReadForUser($this->nurse_id, 'nurse');
    }

    /**
     * Get critical alert count
     */
    public function getCriticalAlertsCount()
    {
        return StaffAlert::getCriticalCount($this->nurse_id, 'nurse');
    }

    // ========================================
    // OTHER HELPER METHODS
    // ========================================

    /**
     * Get full name
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if nurse is on active duty
     */
    public function isActive()
    {
        return $this->status === 'Active';
    }

    /**
     * Get assigned patient count
     */
    public function getAssignedPatientCount()
    {
        return $this->assignedPatients()->count();
    }

    /**
     * Check if nurse is available for new assignments
     */
    public function isAvailableForAssignment($maxPatients = 10)
    {
        return $this->isActive() && $this->getAssignedPatientCount() < $maxPatients;
    }
}