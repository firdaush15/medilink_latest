<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MedicineInventory extends Model
{
    use HasFactory;

    protected $table = 'medicine_inventory';
    protected $primaryKey = 'medicine_id';

    protected $fillable = [
        'medicine_name',
        'generic_name',
        'brand_name',
        'category',
        'form',
        'strength',
        'quantity_in_stock',
        'reorder_level',
        'unit_price',
        'supplier',
        'batch_number',
        'manufacture_date',
        'expiry_date',
        'storage_instructions',
        'side_effects',
        'contraindications',
        'requires_prescription',
        'is_controlled_substance',
        'status',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'requires_prescription' => 'boolean',
        'is_controlled_substance' => 'boolean',
        'unit_price' => 'decimal:2',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'medicine_id');
    }

    public function prescriptionItems()
    {
        return $this->hasMany(PrescriptionItem::class, 'medicine_id');
    }

    // ========================================
    // STOCK MANAGEMENT METHODS
    // ========================================

    /**
     * Update medicine status based on stock and expiry
     */
    public function updateStatus()
    {
        // Check if expired
        if ($this->expiry_date && $this->expiry_date->isPast()) {
            $this->update(['status' => 'Expired']);
            return;
        }

        // Check stock levels
        if ($this->quantity_in_stock == 0) {
            $this->update(['status' => 'Out of Stock']);
        } elseif ($this->quantity_in_stock <= $this->reorder_level) {
            $this->update(['status' => 'Low Stock']);
        } else {
            $this->update(['status' => 'Active']);
        }
    }

    /**
     * Check if medicine is low stock (not including out of stock)
     */
    public function isLowStock()
    {
        return $this->quantity_in_stock <= $this->reorder_level && $this->quantity_in_stock > 0;
    }

    /**
     * Check if out of stock
     */
    public function isOutOfStock()
    {
        return $this->quantity_in_stock == 0;
    }

    /**
     * Add stock
     */
    public function addStock($quantity, $pharmacistId, $notes = null)
    {
        $this->quantity_in_stock += $quantity;
        $this->save();

        StockMovement::create([
            'medicine_id' => $this->medicine_id,
            'pharmacist_id' => $pharmacistId,
            'movement_type' => 'Stock In',
            'quantity' => $quantity,
            'balance_after' => $this->quantity_in_stock,
            'notes' => $notes,
        ]);

        $this->updateStatus();
    }

    /**
     * Reduce stock
     */
    public function reduceStock($quantity, $pharmacistId, $notes = null)
    {
        if ($this->quantity_in_stock < $quantity) {
            throw new \Exception('Insufficient stock');
        }

        $this->quantity_in_stock -= $quantity;
        $this->save();

        StockMovement::create([
            'medicine_id' => $this->medicine_id,
            'pharmacist_id' => $pharmacistId,
            'movement_type' => 'Dispensed',
            'quantity' => -$quantity,
            'balance_after' => $this->quantity_in_stock,
            'notes' => $notes,
        ]);

        $this->updateStatus();
    }

    // ========================================
    // EXPIRY DATE METHODS - REAL-WORLD HOSPITAL STANDARDS
    // ========================================

    /**
     * Check if medicine is expired
     */
    public function isExpired()
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    /**
     * Check if expiring critically soon (≤ 90 days / 3 months)
     * RED ZONE - Need urgent action
     */
    public function isExpiringCritical()
    {
        if (!$this->expiry_date || $this->isExpired()) {
            return false;
        }

        $now = Carbon::now();
        $daysUntilExpiry = $now->diffInDays($this->expiry_date, false);

        return $daysUntilExpiry >= 0 && $daysUntilExpiry <= 90;
    }

    /**
     * Check if expiring soon (91-180 days / 3-6 months)
     * ORANGE ZONE - Plan for usage/ordering
     */
    public function isExpiringSoon()
    {
        if (!$this->expiry_date || $this->isExpired()) {
            return false;
        }

        $now = Carbon::now();
        $daysUntilExpiry = $now->diffInDays($this->expiry_date, false);

        return $daysUntilExpiry > 90 && $daysUntilExpiry <= 180;
    }

    /**
     * Check if expiry date is safe (> 180 days / 6+ months)
     * GREEN ZONE - Safe stock
     */
    public function isExpirySafe()
    {
        if (!$this->expiry_date || $this->isExpired()) {
            return false;
        }

        $now = Carbon::now();
        $daysUntilExpiry = $now->diffInDays($this->expiry_date, false);

        return $daysUntilExpiry > 180;
    }

    /**
     * Get days until expiry (for display)
     */
    public function getDaysUntilExpiry()
    {
        if (!$this->expiry_date) {
            return 0;
        }

        if ($this->isExpired()) {
            return 0;
        }

        $now = Carbon::now();
        return (int) $now->diffInDays($this->expiry_date, false); // ✅ Cast to integer
    }

    /**
     * Get months until expiry (for display)
     */
    public function getMonthsUntilExpiry()
    {
        if (!$this->expiry_date) {
            return 0;
        }

        if ($this->isExpired()) {
            return 0;
        }

        $now = Carbon::now();
        return $now->diffInMonths($this->expiry_date, false);
    }

    /**
     * Get expiry status for display
     */
    public function getExpiryStatus()
    {
        if ($this->isExpired()) {
            return 'expired';
        } elseif ($this->isExpiringCritical()) {
            return 'critical';
        } elseif ($this->isExpiringSoon()) {
            return 'warning';
        } else {
            return 'safe';
        }
    }

    /**
     * Get expiry badge class
     */
    public function getExpiryBadgeClass()
    {
        return match ($this->getExpiryStatus()) {
            'expired' => 'expired-date',
            'critical' => 'critical-expiry-date',
            'warning' => 'warning-expiry-date',
            'safe' => 'normal-expiry-date',
            default => 'normal-expiry-date',
        };
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope: Active medicines
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope: Low stock medicines
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity_in_stock <= reorder_level')
            ->where('quantity_in_stock', '>', 0);
    }

    /**
     * Scope: Out of stock medicines
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity_in_stock', 0);
    }

    /**
     * Scope: Expired medicines
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<=', Carbon::now());
    }

    /**
     * Scope: Expiring critically (within 90 days)
     */
    public function scopeExpiringCritical($query)
    {
        return $query->where('expiry_date', '<=', Carbon::now()->addDays(90))
            ->where('expiry_date', '>', Carbon::now());
    }

    /**
     * Scope: Expiring soon (91-180 days)
     */
    public function scopeExpiringSoon($query)
    {
        return $query->where('expiry_date', '<=', Carbon::now()->addDays(180))
            ->where('expiry_date', '>', Carbon::now()->addDays(90));
    }

    /**
     * Scope: By category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
