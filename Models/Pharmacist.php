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
        'availability_status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date'     => 'date',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Dispensing records handled by this pharmacist
     * (prescriptions are owned by doctors, not pharmacists)
     */
    public function dispensings()
    {
        return $this->hasMany(PrescriptionDispensing::class, 'pharmacist_id', 'pharmacist_id');
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

    public function getUnreadAlertsCount()
    {
        return $this->alerts()->unread()->count();
    }

    public function getCriticalAlerts()
    {
        return $this->alerts()->critical()->pending()->defaultOrder()->get();
    }

    public function getPendingAlerts()
    {
        return $this->alerts()->pending()->defaultOrder()->get();
    }

    public function getMedicineAlerts()
    {
        return $this->alerts()->whereNotNull('medicine_id')->defaultOrder()->get();
    }

    public function getPrescriptionAlerts()
    {
        return $this->alerts()->whereNotNull('prescription_id')->defaultOrder()->get();
    }

    public function getLowStockAlerts()
    {
        return $this->alerts()->byType('Low Stock')->pending()->defaultOrder()->get();
    }

    public function getTodayAlerts()
    {
        return $this->alerts()->today()->defaultOrder()->get();
    }

    public function getRecentAlerts($limit = 10)
    {
        return $this->alerts()
                    ->where('created_at', '>=', now()->subDay())
                    ->defaultOrder()
                    ->limit($limit)
                    ->get();
    }

    public function markAllAlertsRead()
    {
        return StaffAlert::markAllReadForUser($this->pharmacist_id, 'pharmacist');
    }

    public function getCriticalAlertsCount()
    {
        return StaffAlert::getCriticalCount($this->pharmacist_id, 'pharmacist');
    }

    // ========================================
    // OTHER HELPER METHODS
    // ========================================

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isActive()
    {
        return $this->status === 'Active';
    }

    /**
     * ✅ FIXED: query prescription_dispensings (not prescriptions.pharmacist_id)
     */
    public function getTotalPrescriptionsDispensed()
    {
        return $this->dispensings()
                    ->where('verification_status', 'Dispensed')
                    ->count();
    }

    /**
     * ✅ FIXED: pending = dispensings where status is Pending or Verified (not yet dispensed)
     */
    public function getPendingPrescriptionsCount()
    {
        return $this->dispensings()
                    ->whereIn('verification_status', ['Pending', 'Verified'])
                    ->count();
    }

    public function getRecentStockMovements($days = 7)
    {
        return $this->stockMovements()
                    ->where('created_at', '>=', now()->subDays($days))
                    ->orderBy('created_at', 'desc')
                    ->get();
    }
}