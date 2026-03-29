<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MedicineBatch extends Model
{
    use HasFactory;

    protected $table = 'medicine_batches';
    protected $primaryKey = 'batch_id';

    protected $fillable = [
        'medicine_id',
        'batch_number',
        'quantity',
        'supplier',
        'manufacture_date',
        'expiry_date',
        'received_date',
        'unit_price',
        'status',
        'notes',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'received_date' => 'date',
        'unit_price' => 'decimal:2',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function medicine()
    {
        return $this->belongsTo(MedicineInventory::class, 'medicine_id', 'medicine_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'batch_id', 'batch_id');
    }

    // ========================================
    // EXPIRY METHODS
    // ========================================

    public function isExpired()
    {
        return $this->expiry_date->isPast();
    }

    public function isExpiringCritical()
    {
        return !$this->isExpired() && $this->expiry_date->lte(now()->addDays(90));
    }

    public function isExpiringSoon()
    {
        return !$this->isExpired() && $this->expiry_date->lte(now()->addDays(180)) && !$this->isExpiringCritical();
    }

    public function getDaysUntilExpiry()
    {
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getMonthsUntilExpiry()
    {
        return now()->diffInMonths($this->expiry_date, false);
    }

    // ========================================
    // STATUS METHODS
    // ========================================

    public function isDepleted()
    {
        return $this->quantity <= 0;
    }

    public function updateStatus()
    {
        if ($this->isExpired()) {
            $this->status = 'expired';
        } elseif ($this->isDepleted()) {
            $this->status = 'depleted';
        } else {
            $this->status = 'active';
        }
        
        $this->save();
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('quantity', '>', 0);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<=', now());
    }

    public function scopeExpiringSoon($query, $days = 180)
    {
        return $query->where('expiry_date', '>', now())
                    ->where('expiry_date', '<=', now()->addDays($days));
    }

    public function scopeOldestFirst($query)
    {
        return $query->orderBy('expiry_date', 'asc');
    }
}