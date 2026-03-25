<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $primaryKey = 'doctor_id';

    protected $fillable = [
        'user_id',
        'phone_number',
        'profile_photo',
        'specialization',
        'availability_status',
    ];

    // ========================================
    // BASIC RELATIONSHIPS
    // ========================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'doctor_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'doctor_id');
    }

    // ✅ FIXED: Updated to use LeaveRequest instead of DoctorLeave
    public function leaves()
    {
        return $this->hasMany(LeaveRequest::class, 'user_id', 'user_id')
            ->where('staff_role', 'doctor');
    }

    public function ratings()
    {
        return $this->hasMany(DoctorRating::class, 'doctor_id');
    }

    /**
     * All diagnoses made by this doctor
     */
    public function diagnoses()
    {
        return $this->hasMany(PatientDiagnosis::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Get diagnosis statistics
     */
    public function getDiagnosisStats($startDate = null, $endDate = null)
    {
        $query = $this->diagnoses();

        if ($startDate && $endDate) {
            $query->whereBetween('diagnosis_date', [$startDate, $endDate]);
        }

        return [
            'total' => $query->count(),
            'primary' => $query->where('diagnosis_type', 'Primary')->count(),
            'confirmed' => $query->where('certainty', 'Confirmed')->count(),
            'active' => $query->where('status', 'Active')->count(),
            'resolved' => $query->where('status', 'Resolved')->count(),
        ];
    }

    // ========================================
    // ✅ NURSE ASSIGNMENT RELATIONSHIPS (FIXED WITH PIVOT MODEL)
    // ========================================

    /**
     * Get all nurse-doctor assignment records
     */
    public function nurseAssignments()
    {
        return $this->hasMany(NurseDoctorAssignment::class, 'doctor_id');
    }

    /**
     * Get assigned nurses (with pivot data)
     * ✅ CRITICAL: using() tells Laravel to use the NurseDoctorAssignment model for pivot
     */
    public function assignedNurses()
    {
        return $this->belongsToMany(Nurse::class, 'nurse_doctor_assignments', 'doctor_id', 'nurse_id')
            ->using(NurseDoctorAssignment::class)  // ✅ THIS IS THE KEY FIX!
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
            ->where('nurse_doctor_assignments.is_active', true)
            ->orderBy('nurse_doctor_assignments.priority_order');
    }

    /**
     * Get all assigned nurses (including inactive)
     */
    public function allAssignedNurses()
    {
        return $this->belongsToMany(Nurse::class, 'nurse_doctor_assignments', 'doctor_id', 'nurse_id')
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
            ->orderBy('nurse_doctor_assignments.priority_order');
    }

    /**
     * Get primary assigned nurses only
     */
    public function primaryNurses()
    {
        return $this->belongsToMany(Nurse::class, 'nurse_doctor_assignments', 'doctor_id', 'nurse_id')
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
            ->where('nurse_doctor_assignments.is_active', true)
            ->where('nurse_doctor_assignments.assignment_type', 'primary')
            ->orderBy('nurse_doctor_assignments.priority_order');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    public function hasAssignedNurses()
    {
        return $this->assignedNurses()->count() > 0;
    }

    public function getAssignedNursesCount()
    {
        return $this->assignedNurses()->count();
    }

    public function isAvailable()
    {
        return $this->availability_status === 'Available';
    }

    public function getFullNameAttribute()
    {
        return "Dr. {$this->user->name}";
    }

    // ========================================
    // ✅ NEW LEAVE HELPER METHODS
    // ========================================

    /**
     * Check if doctor is currently on approved leave
     */
    public function isOnLeave()
    {
        return $this->leaves()
            ->where('status', 'approved')
            ->where('start_date', '<=', today())
            ->where('end_date', '>=', today())
            ->exists();
    }

    /**
     * Get current active leave if any
     */
    public function currentLeave()
    {
        return $this->leaves()
            ->where('status', 'approved')
            ->where('start_date', '<=', today())
            ->where('end_date', '>=', today())
            ->first();
    }

    /**
     * Get upcoming leaves
     */
    public function upcomingLeaves()
    {
        return $this->leaves()
            ->where('status', 'approved')
            ->where('start_date', '>', today())
            ->orderBy('start_date', 'asc')
            ->get();
    }
}
