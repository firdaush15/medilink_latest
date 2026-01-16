<?php
// app/Models/LeaveRequest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $primaryKey = 'leave_id';
    
    protected $fillable = [
        'user_id',
        'staff_role',
        'start_date',
        'end_date',
        'days',
        'is_half_day',
        'leave_type',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'admin_notes',
        'rejection_reason',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'days' => 'decimal:1',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function getTotalDays()
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public static function hasBalance($userId, $leaveType, $requestedDays)
    {
        if ($leaveType === 'Unpaid Leave') {
            return true;
        }

        $total = match($leaveType) {
            'Annual Leave' => 14,
            'Sick Leave' => 14,
            'Emergency Leave' => 5,
            default => 14,
        };

        $used = self::where('user_id', $userId)
            ->where('leave_type', $leaveType)
            ->where('status', 'approved')
            ->whereYear('start_date', date('Y'))
            ->sum('days');

        return ($total - $used) >= $requestedDays;
    }
}