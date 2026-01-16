<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NurseWorkloadTracking extends Model
{
    protected $primaryKey = 'tracking_id';
    protected $table = 'nurse_workload_tracking';

    protected $fillable = [
        'nurse_id',
        'current_patients',
        'pending_vitals',
        'total_today',
        'max_capacity',
        'is_available',
        'current_status',
        'avg_completion_time_minutes',
        'efficiency_score',
        'last_assignment_at',
        'last_completed_at',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'efficiency_score' => 'decimal:2',
        'last_assignment_at' => 'datetime',
        'last_completed_at' => 'datetime',
    ];

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_id');
    }

    public function isAtCapacity()
    {
        return $this->current_patients >= $this->max_capacity;
    }

    public function getWorkloadPercentage()
    {
        return ($this->current_patients / $this->max_capacity) * 100;
    }
}