<?php
// app/Models/StaffShift.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StaffShift extends Model
{
    protected $primaryKey = 'shift_id';
    
    protected $fillable = [
        'user_id',
        'staff_role',
        'template_id',
        'shift_date',
        'start_time',
        'end_time',
        'status',
        'actual_check_in',
        'actual_check_out',
        'notes',
        'assigned_by',
        'is_recurring',
        'recurrence_pattern',
    ];
    
    protected $casts = [
        'shift_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'actual_check_in' => 'datetime',
        'actual_check_out' => 'datetime',
        'is_recurring' => 'boolean',
    ];
    
    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function template()
    {
        return $this->belongsTo(ShiftTemplate::class, 'template_id');
    }
    
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
    
    // Helpers
    public function isToday()
    {
        return $this->shift_date->isToday();
    }
    
    public function isActive()
    {
        return $this->status === 'checked_in';
    }
    
    public function getDurationHours()
    {
        return Carbon::parse($this->start_time)->diffInHours(Carbon::parse($this->end_time));
    }
}