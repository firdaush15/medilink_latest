<?php
// app/Models/LeaveEntitlement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveEntitlement extends Model
{
    protected $fillable = [
        'user_id',
        'year',
        'annual_leave_days',
        'sick_leave_days',
        'emergency_leave_days',
    ];
    
    protected $casts = [
        'year' => 'integer',
    ];
    
    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // ========================================
    // HELPER METHODS
    // ========================================
    
    /**
     * Get or create entitlement for user and year
     */
    public static function getForUser($userId, $year = null)
    {
        $year = $year ?? date('Y');
        
        return self::firstOrCreate(
            ['user_id' => $userId, 'year' => $year],
            [
                'annual_leave_days' => 14,
                'sick_leave_days' => 14,
                'emergency_leave_days' => 5,
            ]
        );
    }
    
    /**
     * Get used leave days by type
     */
    public function getUsedDays($leaveType)
    {
        return LeaveRequest::where('user_id', $this->user_id)
            ->where('leave_type', $leaveType)
            ->where('status', 'approved')
            ->whereYear('start_date', $this->year)
            ->sum('days');
    }
    
    /**
     * Get remaining leave days by type
     */
    public function getRemainingDays($leaveType)
    {
        $entitlement = match($leaveType) {
            'Annual Leave' => $this->annual_leave_days,
            'Sick Leave' => $this->sick_leave_days,
            'Emergency Leave' => $this->emergency_leave_days,
            default => 0,
        };
        
        return max(0, $entitlement - $this->getUsedDays($leaveType));
    }
    
    /**
     * Get complete leave balance summary
     */
    public function getBalanceSummary()
    {
        return [
            'annual' => [
                'entitled' => $this->annual_leave_days,
                'used' => $this->getUsedDays('Annual Leave'),
                'remaining' => $this->getRemainingDays('Annual Leave'),
            ],
            'sick' => [
                'entitled' => $this->sick_leave_days,
                'used' => $this->getUsedDays('Sick Leave'),
                'remaining' => $this->getRemainingDays('Sick Leave'),
            ],
            'emergency' => [
                'entitled' => $this->emergency_leave_days,
                'used' => $this->getUsedDays('Emergency Leave'),
                'remaining' => $this->getRemainingDays('Emergency Leave'),
            ],
            'total_used' => LeaveRequest::where('user_id', $this->user_id)
                ->where('status', 'approved')
                ->whereYear('start_date', $this->year)
                ->sum('days'),
        ];
    }
    
    /**
     * Check if user has sufficient balance
     */
    public function hasSufficientBalance($leaveType, $daysRequested)
    {
        if ($leaveType === 'Unpaid Leave') {
            return true; // Unpaid leave always allowed
        }
        
        return $this->getRemainingDays($leaveType) >= $daysRequested;
    }
    
    /**
     * Adjust entitlement (for admin modifications)
     */
    public function adjustEntitlement($leaveType, $days)
    {
        match($leaveType) {
            'Annual Leave' => $this->update(['annual_leave_days' => $days]),
            'Sick Leave' => $this->update(['sick_leave_days' => $days]),
            'Emergency Leave' => $this->update(['emergency_leave_days' => $days]),
        };
    }
}