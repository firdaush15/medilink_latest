<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientNurseAssignment extends Model
{
    protected $primaryKey = 'assignment_id';

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'nurse_id',
        'assignment_method',
        'assigned_at',
        'assigned_by',
        'status',
        'accepted_at',
        'started_at',
        'completed_at',
        'transferred_to',
        'transfer_reason',
        'transferred_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'transferred_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}