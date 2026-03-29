<?php
// app/Models/Admin.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $primaryKey = 'admin_id';

    protected $fillable = [
        'user_id',
        'phone_number',
        'profile_photo',
        'employee_id',
        'admin_level',
        'department',
        'hire_date',
        'permissions',
        'can_manage_staff',
        'can_manage_inventory',
        'can_manage_billing',
        'can_view_reports',
        'can_manage_system_settings',
        'status',
        'last_login_at',
        'last_login_ip',
        'total_logins',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'permissions' => 'array',
        'can_manage_staff' => 'boolean',
        'can_manage_inventory' => 'boolean',
        'can_manage_billing' => 'boolean',
        'can_view_reports' => 'boolean',
        'can_manage_system_settings' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by', 'user_id');
    }

    public function approvedRestockRequests()
    {
        return $this->hasMany(RestockRequest::class, 'approved_by', 'user_id');
    }

    public function alerts()
    {
        return $this->hasMany(StaffAlert::class, 'recipient_id')
            ->where('recipient_type', 'admin');
    }

    // ========================================
    // PERMISSION HELPERS
    // ========================================

    /**
     * Check if admin is Super Admin
     */
    public function isSuperAdmin()
    {
        return $this->admin_level === 'Super Admin';
    }

    /**
     * Check if admin is System Admin
     */
    public function isSystemAdmin()
    {
        return $this->admin_level === 'System Admin';
    }

    /**
     * Check if admin is active
     */
    public function isActive()
    {
        return $this->status === 'Active';
    }

    /**
     * Check if admin can perform specific action
     */
    public function can($action)
    {
        // Super Admin can do everything
        if ($this->isSuperAdmin()) {
            return true;
        }

        return match($action) {
            'manage_staff' => $this->can_manage_staff,
            'manage_inventory' => $this->can_manage_inventory,
            'manage_billing' => $this->can_manage_billing,
            'view_reports' => $this->can_view_reports,
            'manage_system_settings' => $this->can_manage_system_settings,
            default => false,
        };
    }

    /**
     * Record login activity
     */
    public function recordLogin($ipAddress = null)
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress ?? request()->ip(),
            'total_logins' => $this->total_logins + 1,
        ]);
    }

    // ========================================
    // GETTER METHODS
    // ========================================

    /**
     * Get full name
     */
    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    /**
     * Get admin level badge class
     */
    public function getAdminLevelBadgeClass()
    {
        return match ($this->admin_level) {
            'Super Admin' => 'badge-danger',
            'System Admin' => 'badge-warning',
            'Admin' => 'badge-primary',
            default => 'badge-secondary',
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            'Active' => 'badge-success',
            'On Leave' => 'badge-warning',
            'Inactive' => 'badge-secondary',
            default => 'badge-light',
        };
    }

    // ========================================
    // ALERT METHODS
    // ========================================

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
     * Get pending alerts
     */
    public function getPendingAlerts()
    {
        return $this->alerts()
            ->pending()
            ->defaultOrder()
            ->get();
    }

    // ========================================
    // STATISTICS METHODS
    // ========================================

    /**
     * Get today's approved leave requests count
     */
    public function getTodayApprovedLeaves()
    {
        return $this->approvedLeaveRequests()
            ->whereDate('approved_at', today())
            ->count();
    }

    /**
     * Get pending leave requests count
     */
    public function getPendingLeaveRequestsCount()
    {
        return LeaveRequest::where('status', 'pending')->count();
    }

    /**
     * Get pending restock requests count
     */
    public function getPendingRestockRequestsCount()
    {
        return RestockRequest::where('status', 'Pending')->count();
    }

    /**
     * Get today's activity summary
     */
    public function getTodayActivitySummary()
    {
        return [
            'leaves_approved' => $this->getTodayApprovedLeaves(),
            'pending_leaves' => $this->getPendingLeaveRequestsCount(),
            'pending_restocks' => $this->getPendingRestockRequestsCount(),
            'critical_alerts' => $this->getCriticalAlerts()->count(),
        ];
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope: Active admins
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope: Super admins only
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('admin_level', 'Super Admin');
    }

    /**
     * Scope: System admins
     */
    public function scopeSystemAdmins($query)
    {
        return $query->where('admin_level', 'System Admin');
    }

    /**
     * Scope: Regular admins
     */
    public function scopeRegularAdmins($query)
    {
        return $query->where('admin_level', 'Admin');
    }

    /**
     * Scope: Recently logged in (last 7 days)
     */
    public function scopeRecentlyActive($query)
    {
        return $query->where('last_login_at', '>=', now()->subDays(7));
    }
}