<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'address',
        'profile_photo',
        'last_seen_at',
        'account_completed',
        'registered_by_staff',
        'account_completion_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ========================================
    // ROLE-SPECIFIC RELATIONSHIPS
    // ========================================

    /**
     * Admin relationship
     */
    public function admin()
    {
        return $this->hasOne(Admin::class, 'user_id');
    }

    /**
     * Doctor relationship
     */
    public function doctor()
    {
        return $this->hasOne(Doctor::class, 'user_id');
    }

    /**
     * Nurse relationship
     */
    public function nurse()
    {
        return $this->hasOne(Nurse::class, 'user_id');
    }

    /**
     * Pharmacist relationship
     */
    public function pharmacist()
    {
        return $this->hasOne(Pharmacist::class, 'user_id');
    }

    /**
     * Receptionist relationship
     */
    public function receptionist()
    {
        return $this->hasOne(Receptionist::class, 'user_id');
    }

    /**
     * Patient relationship
     */
    public function patient()
    {
        return $this->hasOne(Patient::class, 'user_id');
    }

    // ========================================
    // OTHER RELATIONSHIPS
    // ========================================

    /**
     * Messages sent by this user
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get user's leave entitlement for current year
     */
    public function leaveEntitlement()
    {
        return $this->hasOne(LeaveEntitlement::class)
            ->where('year', date('Y'));
    }

    /**
     * Get all leave entitlements (historical)
     */
    public function leaveEntitlements()
    {
        return $this->hasMany(LeaveEntitlement::class);
    }

    /**
     * Get leave requests
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get staff shifts
     */
    public function staffShifts()
    {
        return $this->hasMany(StaffShift::class);
    }

    /**
     * Get tasks assigned to this user
     */
    public function assignedTasks()
    {
        return $this->hasMany(StaffTask::class, 'assigned_to_id');
    }

    /**
     * Get tasks created by this user
     */
    public function createdTasks()
    {
        return $this->hasMany(StaffTask::class, 'assigned_by_id');
    }

    /**
     * Get system notifications
     */
    public function notifications()
    {
        return $this->hasMany(SystemNotification::class, 'user_id');
    }

    // ========================================
    // ATTRIBUTE CASTING
    // ========================================

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
            'account_completed' => 'boolean',
            'registered_by_staff' => 'boolean',
        ];
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get the role-specific model instance
     * Returns Admin, Doctor, Nurse, Pharmacist, Receptionist, or Patient model
     */
    public function getRoleModel()
    {
        return match($this->role) {
            'admin' => $this->admin,
            'doctor' => $this->doctor,
            'nurse' => $this->nurse,
            'pharmacist' => $this->pharmacist,
            'receptionist' => $this->receptionist,
            'patient' => $this->patient,
            default => null,
        };
    }

    /**
     * Get role display name with emoji
     */
    public function getRoleDisplayAttribute()
    {
        return match($this->role) {
            'admin' => '👑 Admin',
            'doctor' => '👨‍⚕️ Doctor',
            'nurse' => '👩‍⚕️ Nurse',
            'pharmacist' => '💊 Pharmacist',
            'receptionist' => '📋 Receptionist',
            'patient' => '🧑‍🦱 Patient',
            default => ucfirst($this->role),
        };
    }

    /**
     * Get role badge CSS class
     */
    public function getRoleBadgeClass()
    {
        return match($this->role) {
            'admin' => 'badge-danger',
            'doctor' => 'badge-primary',
            'nurse' => 'badge-success',
            'pharmacist' => 'badge-info',
            'receptionist' => 'badge-warning',
            'patient' => 'badge-secondary',
            default => 'badge-light',
        };
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is doctor
     */
    public function isDoctor()
    {
        return $this->role === 'doctor';
    }

    /**
     * Check if user is nurse
     */
    public function isNurse()
    {
        return $this->role === 'nurse';
    }

    /**
     * Check if user is pharmacist
     */
    public function isPharmacist()
    {
        return $this->role === 'pharmacist';
    }

    /**
     * Check if user is receptionist
     */
    public function isReceptionist()
    {
        return $this->role === 'receptionist';
    }

    /**
     * Check if user is patient
     */
    public function isPatient()
    {
        return $this->role === 'patient';
    }

    /**
     * Check if user is staff (not patient)
     */
    public function isStaff()
    {
        return in_array($this->role, ['admin', 'doctor', 'nurse', 'pharmacist', 'receptionist']);
    }

    // ========================================
    // ONLINE STATUS METHODS
    // ========================================

    /**
     * Check if user is online
     * User is considered online if they were active in the last 5 minutes
     */
    public function isOnline()
    {
        return $this->last_seen_at &&
            $this->last_seen_at->greaterThan(now()->subMinutes(5));
    }

    /**
     * Update last seen timestamp
     */
    public function updateLastSeen()
    {
        $this->update(['last_seen_at' => now()]);
    }

    /**
     * Get last seen time (HH:mm format)
     */
    public function getLastSeenTimeAttribute()
    {
        if (!$this->last_seen_at) {
            return null;
        }

        return $this->last_seen_at->format('H:i');
    }

    /**
     * Get last seen with context
     * Returns "Last seen at 14:00" or "Last seen 2 hours ago"
     */
    public function getLastSeenFullAttribute()
    {
        if (!$this->last_seen_at) {
            return null;
        }

        if ($this->last_seen_at->isToday()) {
            return 'Last seen at ' . $this->last_seen_at->format('H:i');
        }

        return 'Last seen ' . $this->last_seen_at->diffForHumans();
    }

    // ========================================
    // LEAVE MANAGEMENT METHODS
    // ========================================

    /**
     * Get or create entitlement for current year
     */
    public function getCurrentLeaveEntitlement()
    {
        return LeaveEntitlement::getForUser($this->id);
    }

    /**
     * Quick access to leave balance
     */
    public function getLeaveBalance()
    {
        return $this->getCurrentLeaveEntitlement()->getBalanceSummary();
    }

    /**
     * Check if user has sufficient leave balance
     */
    public function hasLeaveBalance($leaveType, $days)
    {
        return LeaveRequest::hasBalance($this->id, $leaveType, $days);
    }

    /**
     * Get pending leave requests count
     */
    public function getPendingLeaveRequestsCount()
    {
        return $this->leaveRequests()
            ->where('status', 'pending')
            ->count();
    }

    // ========================================
    // ACCOUNT COMPLETION METHODS
    // ========================================

    /**
     * Check if account setup is completed
     */
    public function hasCompletedAccount()
    {
        return $this->account_completed === true;
    }

    /**
     * Check if account was registered by staff
     */
    public function wasRegisteredByStaff()
    {
        return $this->registered_by_staff === true;
    }

    /**
     * Generate account completion token
     */
    public function generateCompletionToken()
    {
        $token = \Str::random(64);
        $this->update(['account_completion_token' => $token]);
        return $token;
    }

    /**
     * Mark account as completed
     */
    public function markAccountAsCompleted()
    {
        $this->update([
            'account_completed' => true,
            'account_completion_token' => null,
        ]);
    }

    // ========================================
    // NOTIFICATION METHODS
    // ========================================

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCount()
    {
        return $this->notifications()
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get unread critical notifications
     */
    public function getCriticalNotifications()
    {
        return $this->notifications()
            ->where('is_read', false)
            ->where('priority', 'urgent')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead()
    {
        return $this->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    // ========================================
    // TASK MANAGEMENT METHODS
    // ========================================

    /**
     * Get active tasks count
     */
    public function getActiveTasksCount()
    {
        return $this->assignedTasks()
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();
    }

    /**
     * Get overdue tasks
     */
    public function getOverdueTasks()
    {
        return $this->assignedTasks()
            ->where('due_at', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('due_at', 'asc')
            ->get();
    }

    /**
     * Get today's tasks
     */
    public function getTodayTasks()
    {
        return $this->assignedTasks()
            ->whereDate('due_at', today())
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderByPriority()
            ->get();
    }

    // ========================================
    // SHIFT MANAGEMENT METHODS
    // ========================================

    /**
     * Get current shift (if on duty today)
     */
    public function getCurrentShift()
    {
        return $this->staffShifts()
            ->where('shift_date', today())
            ->where('status', 'checked_in')
            ->first();
    }

    /**
     * Check if user is on shift today
     */
    public function isOnShiftToday()
    {
        return $this->staffShifts()
            ->where('shift_date', today())
            ->exists();
    }

    /**
     * Get today's shift
     */
    public function getTodayShift()
    {
        return $this->staffShifts()
            ->where('shift_date', today())
            ->first();
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope: Filter by role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope: Staff members only (exclude patients)
     */
    public function scopeStaff($query)
    {
        return $query->whereIn('role', ['admin', 'doctor', 'nurse', 'pharmacist', 'receptionist']);
    }

    /**
     * Scope: Patients only
     */
    public function scopePatients($query)
    {
        return $query->where('role', 'patient');
    }

    /**
     * Scope: Online users (active in last 5 minutes)
     */
    public function scopeOnline($query)
    {
        return $query->where('last_seen_at', '>=', now()->subMinutes(5));
    }

    /**
     * Scope: Users with completed accounts
     */
    public function scopeCompleted($query)
    {
        return $query->where('account_completed', true);
    }

    /**
     * Scope: Users with incomplete accounts
     */
    public function scopeIncomplete($query)
    {
        return $query->where('account_completed', false);
    }

    /**
     * Scope: Users registered by staff
     */
    public function scopeRegisteredByStaff($query)
    {
        return $query->where('registered_by_staff', true);
    }
}