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
        'storage_instructions',
        'side_effects',
        'contraindications',
        'requires_prescription',
        'is_controlled_substance',
        'status',
    ];

    protected $casts = [
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

    public function batches()
    {
        return $this->hasMany(MedicineBatch::class, 'medicine_id', 'medicine_id');
    }

    public function activeBatches()
    {
        return $this->hasMany(MedicineBatch::class, 'medicine_id', 'medicine_id')
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc'); // FEFO: First Expired First Out
    }

    // ========================================
    // STOCK CALCULATION (from batches)
    // ========================================

    /**
     * Get total stock from all active batches
     * Can be accessed as $medicine->total_stock
     */
    public function getTotalStockAttribute()
    {
        return $this->activeBatches()->sum('quantity');
    }

    /**
     * Recalculate stock from batches and update the record
     */
    public function recalculateStock()
    {
        $totalStock = $this->activeBatches()->sum('quantity');
        $this->quantity_in_stock = $totalStock;
        $this->save();

        return $totalStock;
    }

    /**
     * Update medicine status based on stock levels
     */
    public function updateStatus()
    {
        // Recalculate stock from batches first
        $this->recalculateStock();

        if ($this->quantity_in_stock == 0) {
            $this->status = 'Out of Stock';
        } elseif ($this->quantity_in_stock <= $this->reorder_level) {
            $this->status = 'Low Stock';
        } else {
            $this->status = 'Active';
        }

        $this->save();
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

    // ========================================
    // EXPIRY METHODS (checks batches)
    // ========================================

    /**
     * Check if ANY batch is expired
     */
    public function hasExpiredBatches()
    {
        return $this->batches()
            ->where('expiry_date', '<=', now())
            ->where('quantity', '>', 0)
            ->exists();
    }

    /**
     * Check if ANY batch is expiring within specified days
     */
    public function hasExpiringBatches($days = 180)
    {
        return $this->batches()
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('quantity', '>', 0)
            ->exists();
    }

    /**
     * Get the next expiring batch (for display)
     * This is the batch that will expire first
     */
    public function getNextExpiringBatch()
    {
        return $this->activeBatches()
            ->orderBy('expiry_date', 'asc')
            ->first();
    }

    /**
     * For backward compatibility with existing views
     * Check if medicine has expired batches
     */
    public function isExpired()
    {
        return $this->hasExpiredBatches();
    }

    /**
     * Check if expiring critically (≤ 90 days / 3 months)
     * RED ZONE - Need urgent action
     */
    public function isExpiringCritical()
    {
        $batch = $this->getNextExpiringBatch();
        if (!$batch) return false;

        $daysLeft = now()->diffInDays($batch->expiry_date, false);
        return $daysLeft >= 0 && $daysLeft <= 90;
    }

    /**
     * Check if expiring soon (91-180 days / 3-6 months)
     * ORANGE ZONE - Plan for usage/ordering
     */
    public function isExpiringSoon()
    {
        $batch = $this->getNextExpiringBatch();
        if (!$batch) return false;

        $daysLeft = now()->diffInDays($batch->expiry_date, false);
        return $daysLeft > 90 && $daysLeft <= 180;
    }

    /**
     * Get days until next batch expiry (for display)
     */
    public function getDaysUntilExpiry()
    {
        $batch = $this->getNextExpiringBatch();
        if (!$batch) return 0;

        $daysLeft = now()->diffInDays($batch->expiry_date, false);
        return max(0, $daysLeft);
    }

    /**
     * Get months until next batch expiry (for display)
     */
    public function getMonthsUntilExpiry()
    {
        $batch = $this->getNextExpiringBatch();
        if (!$batch) return 0;

        $monthsLeft = now()->diffInMonths($batch->expiry_date, false);
        // Cast to (int) or use round() to ensure whole numbers
        return (int) max(0, $monthsLeft);
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
    // FEFO (First Expired First Out) SYSTEM
    // ========================================

    /**
     * Get batches for dispensing using FEFO
     * Automatically selects oldest batches first
     * 
     * @param int $quantityNeeded How many units needed
     * @return array ['batches' => [...], 'total_available' => int, 'shortage' => int]
     */
    public function getAvailableBatchesForDispensing($quantityNeeded)
    {
        $batches = $this->activeBatches()->get(); // Already ordered by expiry_date ASC

        $selectedBatches = [];
        $remainingNeeded = $quantityNeeded;

        foreach ($batches as $batch) {
            if ($remainingNeeded <= 0) break;

            $quantityFromBatch = min($batch->quantity, $remainingNeeded);

            $selectedBatches[] = [
                'batch' => $batch,
                'quantity' => $quantityFromBatch,
            ];

            $remainingNeeded -= $quantityFromBatch;
        }

        return [
            'batches' => $selectedBatches,
            'total_available' => $quantityNeeded - $remainingNeeded,
            'shortage' => max(0, $remainingNeeded),
        ];
    }

    // ========================================
    // STOCK MANAGEMENT METHODS
    // ========================================

    /**
     * Reduce stock using FEFO (First Expired First Out)
     * Automatically selects from oldest batches first
     * 
     * @param int $quantity How many units to dispense
     * @param int $pharmacistId Who is dispensing
     * @param string $notes Reason for stock reduction
     * @return array Information about dispensed batches
     */
    public function reduceStock($quantity, $pharmacistId, $notes = null)
    {
        if ($quantity <= 0) {
            throw new \Exception('Quantity must be greater than 0');
        }

        // Get available batches using FEFO
        $batchResult = $this->getAvailableBatchesForDispensing($quantity);

        if ($batchResult['shortage'] > 0) {
            throw new \Exception(
                "Insufficient stock. Available: {$batchResult['total_available']}, Required: {$quantity}"
            );
        }

        $dispensedBatches = [];

        // Reduce stock from each batch
        foreach ($batchResult['batches'] as $batchInfo) {
            $batch = $batchInfo['batch'];
            $quantityFromBatch = $batchInfo['quantity'];

            // Reduce batch quantity
            $batch->quantity -= $quantityFromBatch;
            $batch->save();
            $batch->updateStatus(); // Update status if depleted

            // Record stock movement
            StockMovement::create([
                'medicine_id' => $this->medicine_id,
                'batch_id' => $batch->batch_id,
                'pharmacist_id' => $pharmacistId,
                'movement_type' => 'Dispensed',
                'quantity' => -$quantityFromBatch,
                'balance_after' => $batch->quantity,
                'batch_number' => $batch->batch_number,
                'notes' => $notes,
            ]);

            $dispensedBatches[] = [
                'batch_id' => $batch->batch_id,
                'batch_number' => $batch->batch_number,
                'quantity' => $quantityFromBatch,
                'expiry_date' => $batch->expiry_date,
            ];
        }

        // Update total stock and status
        $this->recalculateStock();
        $this->updateStatus();

        return $dispensedBatches;
    }

    /**
     * Add stock to inventory (for receiving new stock)
     * 
     * @param int $quantity
     * @param int $pharmacistId
     * @param string $notes
     */
    public function addStock($quantity, $pharmacistId, $notes = null)
    {
        if ($quantity <= 0) {
            throw new \Exception('Quantity must be greater than 0');
        }

        // Note: When adding stock, you should create a new batch
        // This method is kept for backward compatibility
        // In practice, use StockReceipt to add new batches

        $this->quantity_in_stock += $quantity;
        $this->save();
        $this->updateStatus();

        // Record stock movement
        StockMovement::create([
            'medicine_id' => $this->medicine_id,
            'pharmacist_id' => $pharmacistId,
            'movement_type' => 'Stock In',
            'quantity' => $quantity,
            'balance_after' => $this->quantity_in_stock,
            'notes' => $notes,
        ]);
    }

    /**
     * Check if sufficient stock is available
     * 
     * @param int $quantity
     * @return bool
     */
    public function hasSufficientStock($quantity)
    {
        return $this->quantity_in_stock >= $quantity;
    }

    /**
     * Get the batch information that will be used for dispensing
     * Useful for showing to pharmacist before confirming
     * 
     * @param int $quantity
     * @return array
     */
    public function getDispensePreview($quantity)
    {
        $result = $this->getAvailableBatchesForDispensing($quantity);

        return [
            'can_dispense' => $result['shortage'] === 0,
            'available_quantity' => $result['total_available'],
            'requested_quantity' => $quantity,
            'shortage' => $result['shortage'],
            'batches_to_use' => collect($result['batches'])->map(function ($batchInfo) {
                return [
                    'batch_number' => $batchInfo['batch']->batch_number,
                    'quantity_from_batch' => $batchInfo['quantity'],
                    'expiry_date' => $batchInfo['batch']->expiry_date->format('M d, Y'),
                    'days_until_expiry' => $batchInfo['batch']->getDaysUntilExpiry(),
                ];
            }),
        ];
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
     * Scope: Medicines with expired batches
     */
    public function scopeExpired($query)
    {
        return $query->whereHas('batches', function ($q) {
            $q->where('expiry_date', '<=', now())
                ->where('quantity', '>', 0);
        });
    }

    /**
     * Scope: Medicines with critically expiring batches (≤90 days)
     */
    public function scopeExpiringCritical($query)
    {
        return $query->whereHas('batches', function ($q) {
            $q->where('expiry_date', '<=', now()->addDays(90))
                ->where('expiry_date', '>', now())
                ->where('quantity', '>', 0);
        });
    }

    /**
     * Scope: Medicines with batches expiring soon (91-180 days)
     */
    public function scopeExpiringSoon($query)
    {
        return $query->whereHas('batches', function ($q) {
            $q->where('expiry_date', '<=', now()->addDays(180))
                ->where('expiry_date', '>', now()->addDays(90))
                ->where('quantity', '>', 0);
        });
    }

    /**
     * Scope: By category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
