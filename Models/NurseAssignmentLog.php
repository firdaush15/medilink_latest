<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NurseAssignmentLog extends Model
{
    protected $primaryKey = 'log_id';
    public $timestamps = false;

    protected $fillable = [
        'appointment_id',
        'nurse_id',
        'action',
        'details',
        'workload_at_time',
        'action_at',
        'ip_address',
    ];

    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_id');
    }
}