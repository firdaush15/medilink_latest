<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MedicineInventory extends Model
{
    use HasFactory;

    protected $table      = 'medicine_inventory';
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
        'requires_prescription'    => 'boolean',
        'is_controlled_substance'  => 'boolean',
        'unit_price'               => 'decimal:2',
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
            ->orderBy('expiry_date', 'asc');
    }

    // ========================================
    // STOCK CALCULATION (from batches)
    // ========================================

    public function getTotalStockAttribute()
    {
        return $this->activeBatches()->sum('quantity');
    }

    public function recalculateStock()
    {
        $totalStock              = $this->activeBatches()->sum('quantity');
        $this->quantity_in_stock = $totalStock;
        $this->save();
        return $totalStock;
    }

    /**
     * Update medicine status based on stock levels.
     * ✅ Auto-fires pharmacist alert when status drops to Low Stock or Out of Stock.
     */
    public function updateStatus()
    {
        $previousStatus = $this->status;

        $this->recalculateStock();

        if ($this->quantity_in_stock == 0) {
            $this->status = 'Out of Stock';
        } elseif ($this->quantity_in_stock <= $this->reorder_level) {
            $this->status = 'Low Stock';
        } else {
            $this->status = 'Active';
        }

        $this->save();

        // ✅ Fire low-stock alert ONLY when status first changes to Low Stock or Out of Stock
        if (
            $this->status !== $previousStatus &&
            in_array($this->status, ['Low Stock', 'Out of Stock'])
        ) {
            $this->sendLowStockAlert($this->status);
        }
    }

    /**
     * ✅ NEW: Send low-stock / out-of-stock alert to all pharmacists
     */
    protected function sendLowStockAlert(string $statusType): void
    {
        try {
            $pharmacists = User::where('role', 'pharmacist')->get();

            if ($pharmacists->isEmpty()) {
                return;
            }

            $adminUser = User::where('role', 'admin')->first();
            $senderId  = $adminUser ? $adminUser->id : null;

            if (!$senderId) {
                return; // No admin to act as system sender
            }

            $isOutOfStock = ($statusType === 'Out of Stock');
            $priority     = $isOutOfStock ? 'Critical' : 'Urgent';
            $alertType    = $isOutOfStock ? 'Out of Stock' : 'Low Stock';
            $title        = $isOutOfStock
                ? "🚨 OUT OF STOCK: {$this->medicine_name}"
                : "⚠️ LOW STOCK: {$this->medicine_name}";
            $message      = $isOutOfStock
                ? "Stock for {$this->medicine_name} ({$this->strength} {$this->form}) has reached ZERO. Immediate restock required."
                : "Stock for {$this->medicine_name} ({$this->strength} {$this->form}) is low. " .
                  "Current: {$this->quantity_in_stock} units / Reorder level: {$this->reorder_level} units.";

            foreach ($pharmacists as $pharmacist) {
                // Avoid duplicate alerts within 24 hours for the same medicine + status
                $recentAlert = StaffAlert::where('recipient_id', $pharmacist->id)
                    ->where('alert_type', $alertType)
                    ->where('medicine_id', $this->medicine_id)
                    ->where('created_at', '>=', now()->subHours(24))
                    ->exists();

                if (!$recentAlert) {
                    StaffAlert::create([
                        'sender_id'      => $senderId,
                        'sender_type'    => 'system',
                        'recipient_id'   => $pharmacist->id,
                        'recipient_type' => 'pharmacist',
                        'medicine_id'    => $this->medicine_id,
                        'alert_type'     => $alertType,
                        'priority'       => $priority,
                        'alert_title'    => $title,
                        'alert_message'  => $message,
                        'action_url'     => route('pharmacist.inventory.show', $this->medicine_id),
                    ]);
                }
            }

            // Also alert admin if Out of Stock
            if ($isOutOfStock && $adminUser) {
                $adminAlertExists = StaffAlert::where('recipient_id', $adminUser->id)
                    ->where('alert_type', 'Out of Stock')
                    ->where('medicine_id', $this->medicine_id)
                    ->where('created_at', '>=', now()->subHours(24))
                    ->exists();

                if (!$adminAlertExists) {
                    StaffAlert::create([
                        'sender_id'      => $senderId,
                        'sender_type'    => 'system',
                        'recipient_id'   => $adminUser->id,
                        'recipient_type' => 'admin',
                        'medicine_id'    => $this->medicine_id,
                        'alert_type'     => 'Out of Stock',
                        'priority'       => 'Critical',
                        'alert_title'    => "🚨 CRITICAL: {$this->medicine_name} is Out of Stock",
                        'alert_message'  => "Medicine {$this->medicine_name} has run out completely. Approve pharmacist restock request urgently.",
                        'action_url'     => route('admin.pharmacy-inventory.index'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Low stock alert failed for medicine ' . $this->medicine_id . ': ' . $e->getMessage());
        }
    }

    public function isLowStock()
    {
        return $this->quantity_in_stock <= $this->reorder_level && $this->quantity_in_stock > 0;
    }

    public function isOutOfStock()
    {
        return $this->quantity_in_stock == 0;
    }

    // ========================================
    // EXPIRY METHODS
    // ========================================

    public function hasExpiredBatches()
    {
        return $this->batches()->where('expiry_date', '<=', now())->where('quantity', '>', 0)->exists();
    }

    public function hasExpiringBatches($days = 180)
    {
        return $this->batches()
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('quantity', '>', 0)
            ->exists();
    }

    public function getNextExpiringBatch()
    {
        return $this->activeBatches()->orderBy('expiry_date', 'asc')->first();
    }

    public function isExpired()       { return $this->hasExpiredBatches(); }
    public function isExpiringCritical()
    {
        $batch = $this->getNextExpiringBatch();
        if (!$batch) return false;
        $daysLeft = now()->diffInDays($batch->expiry_date, false);
        return $daysLeft >= 0 && $daysLeft <= 90;
    }

    public function isExpiringSoon()
    {
        $batch = $this->getNextExpiringBatch();
        if (!$batch) return false;
        $daysLeft = now()->diffInDays($batch->expiry_date, false);
        return $daysLeft > 90 && $daysLeft <= 180;
    }

    public function getDaysUntilExpiry()
    {
        $batch = $this->getNextExpiringBatch();
        return $batch ? max(0, now()->diffInDays($batch->expiry_date, false)) : 0;
    }

    public function getMonthsUntilExpiry()
    {
        $batch = $this->getNextExpiringBatch();
        return $batch ? (int) max(0, now()->diffInMonths($batch->expiry_date, false)) : 0;
    }

    public function getExpiryStatus()
    {
        if ($this->isExpired())          return 'expired';
        if ($this->isExpiringCritical()) return 'critical';
        if ($this->isExpiringSoon())     return 'warning';
        return 'safe';
    }

    public function getExpiryBadgeClass()
    {
        return match ($this->getExpiryStatus()) {
            'expired'  => 'expired-date',
            'critical' => 'critical-expiry-date',
            'warning'  => 'warning-expiry-date',
            default    => 'normal-expiry-date',
        };
    }

    // ========================================
    // FEFO DISPENSING
    // ========================================

    public function getAvailableBatchesForDispensing($quantityNeeded)
    {
        $batches         = $this->activeBatches()->get();
        $selectedBatches = [];
        $remainingNeeded = $quantityNeeded;

        foreach ($batches as $batch) {
            if ($remainingNeeded <= 0) break;
            $quantityFromBatch = min($batch->quantity, $remainingNeeded);
            $selectedBatches[] = ['batch' => $batch, 'quantity' => $quantityFromBatch];
            $remainingNeeded  -= $quantityFromBatch;
        }

        return [
            'batches'         => $selectedBatches,
            'total_available' => $quantityNeeded - $remainingNeeded,
            'shortage'        => max(0, $remainingNeeded),
        ];
    }

    public function reduceStock($quantity, $pharmacistId, $notes = null)
    {
        if ($quantity <= 0) throw new \Exception('Quantity must be greater than 0');

        $batchResult = $this->getAvailableBatchesForDispensing($quantity);

        if ($batchResult['shortage'] > 0) {
            throw new \Exception(
                "Insufficient stock. Available: {$batchResult['total_available']}, Required: {$quantity}"
            );
        }

        $dispensedBatches = [];

        foreach ($batchResult['batches'] as $batchInfo) {
            $batch             = $batchInfo['batch'];
            $quantityFromBatch = $batchInfo['quantity'];
            $batch->quantity  -= $quantityFromBatch;
            $batch->save();
            $batch->updateStatus();

            StockMovement::create([
                'medicine_id'  => $this->medicine_id,
                'batch_id'     => $batch->batch_id,
                'pharmacist_id'=> $pharmacistId,
                'movement_type'=> 'Dispensed',
                'quantity'     => -$quantityFromBatch,
                'balance_after'=> $batch->quantity,
                'batch_number' => $batch->batch_number,
                'notes'        => $notes,
            ]);

            $dispensedBatches[] = [
                'batch_id'     => $batch->batch_id,
                'batch_number' => $batch->batch_number,
                'quantity'     => $quantityFromBatch,
                'expiry_date'  => $batch->expiry_date,
            ];
        }

        // ✅ updateStatus() now fires low-stock alert automatically if needed
        $this->recalculateStock();
        $this->updateStatus();

        return $dispensedBatches;
    }

    public function addStock($quantity, $pharmacistId, $notes = null)
    {
        if ($quantity <= 0) throw new \Exception('Quantity must be greater than 0');

        $this->quantity_in_stock += $quantity;
        $this->save();
        $this->updateStatus();

        StockMovement::create([
            'medicine_id'  => $this->medicine_id,
            'pharmacist_id'=> $pharmacistId,
            'movement_type'=> 'Stock In',
            'quantity'     => $quantity,
            'balance_after'=> $this->quantity_in_stock,
            'notes'        => $notes,
        ]);
    }

    public function hasSufficientStock($quantity)
    {
        return $this->quantity_in_stock >= $quantity;
    }

    public function getDispensePreview($quantity)
    {
        $result = $this->getAvailableBatchesForDispensing($quantity);

        return [
            'can_dispense'        => $result['shortage'] === 0,
            'available_quantity'  => $result['total_available'],
            'requested_quantity'  => $quantity,
            'shortage'            => $result['shortage'],
            'batches_to_use'      => collect($result['batches'])->map(function ($batchInfo) {
                return [
                    'batch_number'      => $batchInfo['batch']->batch_number,
                    'quantity_from_batch'=> $batchInfo['quantity'],
                    'expiry_date'       => $batchInfo['batch']->expiry_date->format('M d, Y'),
                    'days_until_expiry' => $batchInfo['batch']->getDaysUntilExpiry(),
                ];
            }),
        ];
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    public function scopeActive($query)          { return $query->where('status', 'Active'); }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity_in_stock <= reorder_level')->where('quantity_in_stock', '>', 0);
    }

    public function scopeOutOfStock($query)      { return $query->where('quantity_in_stock', 0); }

    public function scopeExpired($query)
    {
        return $query->whereHas('batches', fn ($q) =>
            $q->where('expiry_date', '<=', now())->where('quantity', '>', 0));
    }

    public function scopeExpiringCritical($query)
    {
        return $query->whereHas('batches', fn ($q) =>
            $q->where('expiry_date', '<=', now()->addDays(90))
              ->where('expiry_date', '>', now())
              ->where('quantity', '>', 0));
    }

    public function scopeExpiringSoon($query)
    {
        return $query->whereHas('batches', fn ($q) =>
            $q->where('expiry_date', '<=', now()->addDays(180))
              ->where('expiry_date', '>', now()->addDays(90))
              ->where('quantity', '>', 0));
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}