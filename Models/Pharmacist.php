<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmacist extends Model
{
    use HasFactory;

    protected $primaryKey = 'pharmacist_id';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'license_number',
        'phone_number',
        'email',
        'date_of_birth',
        'hire_date',
        'specialization',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'pharmacist_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'pharmacist_id');
    }

    public function alerts()
    {
        return $this->hasMany(StaffAlert::class, 'recipient_id')
                    ->where('recipient_type', 'pharmacist');
    }

    // ========================================
    // ALERT HELPER METHODS
    // ========================================

    /**
     * Get unread alert count
     */
    public function getUnreadAlertsCount()
    {
        return $this->alerts()->unread()->count();
    }

    /**
     * Get critical alerts that need immediate attention
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
     * Get all pending (unacknowledged) alerts
     */
    public function getPendingAlerts()
    {
        return $this->alerts()
                    ->pending()
                    ->defaultOrder()
                    ->get();
    }

    /**
     * Get medicine-related alerts
     */
    public function getMedicineAlerts()
    {
        return $this->alerts()
                    ->whereNotNull('medicine_id')
                    ->defaultOrder()
                    ->get();
    }

    /**
     * Get prescription-related alerts
     */
    public function getPrescriptionAlerts()
    {
        return $this->alerts()
                    ->whereNotNull('prescription_id')
                    ->defaultOrder()
                    ->get();
    }

    /**
     * Get low stock alerts
     */
    public function getLowStockAlerts()
    {
        return $this->alerts()
                    ->byType('Low Stock')
                    ->pending()
                    ->defaultOrder()
                    ->get();
    }

    /**
     * Get today's alerts
     */
    public function getTodayAlerts()
    {
        return $this->alerts()
                    ->today()
                    ->defaultOrder()
                    ->get();
    }

    /**
     * Get recent alerts (last 24 hours)
     */
    public function getRecentAlerts($limit = 10)
    {
        return $this->alerts()
                    ->where('created_at', '>=', now()->subDay())
                    ->defaultOrder()
                    ->limit($limit)
                    ->get();
    }

    /**
     * Mark all alerts as read
     */
    public function markAllAlertsRead()
    {
        return StaffAlert::markAllReadForUser($this->pharmacist_id, 'pharmacist');
    }

    /**
     * Get critical alert count
     */
    public function getCriticalAlertsCount()
    {
        return StaffAlert::getCriticalCount($this->pharmacist_id, 'pharmacist');
    }

    // ========================================
    // OTHER HELPER METHODS
    // ========================================

    /**
     * Get full name
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if pharmacist is active
     */
    public function isActive()
    {
        return $this->status === 'Active';
    }

    /**
     * Get total prescriptions dispensed
     */
    public function getTotalPrescriptionsDispensed()
    {
        return $this->prescriptions()
                    ->where('status', 'Dispensed')
                    ->count();
    }

    /**
     * Get pending prescriptions count
     */
    public function getPendingPrescriptionsCount()
    {
        return $this->prescriptions()
                    ->where('status', 'Pending')
                    ->count();
    }

    /**
     * Get recent stock movements
     */
    public function getRecentStockMovements($days = 7)
    {
        return $this->stockMovements()
                    ->where('created_at', '>=', now()->subDays($days))
                    ->orderBy('created_at', 'desc')
                    ->get();
    }
}