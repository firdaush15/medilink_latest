<?php

// ========================================
// app/Models/MedicineDisposal.php
// ========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineDisposal extends Model
{
    protected $primaryKey = 'disposal_id';
    
    protected $fillable = [
        'disposal_number',
        'medicine_id',
        'quantity_disposed',
        'batch_number',
        'expiry_date',
        'reason',
        'reason_details',
        'disposal_method',
        'disposal_details',
        'disposed_by',
        'witnessed_by',
        'disposal_certificate_path',
        'authorization_document',
        'documentation_notes',
        'estimated_loss',
        'disposed_at',
    ];
    
    protected $casts = [
        'quantity_disposed' => 'integer',
        'expiry_date' => 'date',
        'estimated_loss' => 'decimal:2',
        'disposed_at' => 'datetime',
    ];
    
    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    public function medicine()
    {
        return $this->belongsTo(MedicineInventory::class, 'medicine_id', 'medicine_id');
    }
    
    public function disposedBy()
    {
        return $this->belongsTo(Pharmacist::class, 'disposed_by', 'pharmacist_id');
    }
    
    public function witnessedBy()
    {
        return $this->belongsTo(User::class, 'witnessed_by');
    }
    
    // ========================================
    // AUTO-GENERATE DISPOSAL NUMBER
    // ========================================
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($disposal) {
            if (!$disposal->disposal_number) {
                $year = now()->year;
                $lastDisposal = self::whereYear('created_at', $year)
                    ->latest('disposal_id')
                    ->first();
                
                $number = $lastDisposal ? (int)substr($lastDisposal->disposal_number, -4) + 1 : 1;
                $disposal->disposal_number = "DSP-{$year}-" . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
            
            if (!$disposal->disposed_at) {
                $disposal->disposed_at = now();
            }
        });
        
        static::created(function ($disposal) {
            // Reduce stock from inventory
            $disposal->medicine->quantity_in_stock -= $disposal->quantity_disposed;
            $disposal->medicine->save();
            $disposal->medicine->updateStatus();
            
            // Create stock movement log
            StockMovement::create([
                'medicine_id' => $disposal->medicine_id,
                'pharmacist_id' => $disposal->disposed_by,
                'movement_type' => $disposal->reason === 'Expired' ? 'Expired' : 'Damaged',
                'quantity' => -$disposal->quantity_disposed,
                'balance_after' => $disposal->medicine->quantity_in_stock,
                'batch_number' => $disposal->batch_number,
                'notes' => "Disposal: {$disposal->disposal_number} - {$disposal->reason}",
            ]);
        });
    }
}