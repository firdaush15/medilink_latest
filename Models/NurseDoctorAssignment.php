<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class NurseDoctorAssignment extends Pivot  // ✅ Changed from Model to Pivot
{
    protected $primaryKey = 'assignment_id';
    protected $table = 'nurse_doctor_assignments';

    protected $fillable = [
        'nurse_id',
        'doctor_id',
        'assignment_type',
        'priority_order',
        'working_days',
        'shift_start',
        'shift_end',
        'is_active',
        'assigned_from',
        'assigned_until',
    ];

    protected $casts = [
        'working_days' => 'array',  // ✅ Now this will work!
        'shift_start' => 'datetime:H:i',
        'shift_end' => 'datetime:H:i',
        'is_active' => 'boolean',
        'assigned_from' => 'date',
        'assigned_until' => 'date',
    ];

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_id', 'nurse_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }
}