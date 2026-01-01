<?php

// ========================================
// app/Models/StockReceipt.php
// ========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReceipt extends Model
{
    protected $primaryKey = 'receipt_id';
    
    protected $fillable = [
        'receipt_number',
        'restock_request_id',
        'medicine_id',
        'received_by',
        'quantity_ordered',
        'quantity_received',
        'batch_number',
        'manufacture_date',
        'expiry_date',
        'supplier',
        'supplier_invoice_number',
        'unit_price',
        'total_cost',
        'quality_status',
        'quality_check_notes',
        'packaging_intact',
        'temperature_maintained',
        'expiry_acceptable',
        'received_at',
    ];
    
    protected $casts = [
        'quantity_ordered' => 'integer',
        'quantity_received' => 'integer',
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'packaging_intact' => 'boolean',
        'temperature_maintained' => 'boolean',
        'expiry_acceptable' => 'boolean',
        'received_at' => 'datetime',
    ];
    
    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    public function restockRequest()
    {
        return $this->belongsTo(RestockRequest::class, 'restock_request_id', 'request_id');
    }
    
    public function medicine()
    {
        return $this->belongsTo(MedicineInventory::class, 'medicine_id', 'medicine_id');
    }
    
    public function receivedBy()
    {
        return $this->belongsTo(Pharmacist::class, 'received_by', 'pharmacist_id');
    }
    
    // ========================================
    // AUTO-GENERATE RECEIPT NUMBER
    // ========================================
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($receipt) {
            if (!$receipt->receipt_number) {
                $year = now()->year;
                $lastReceipt = self::whereYear('created_at', $year)
                    ->latest('receipt_id')
                    ->first();
                
                $number = $lastReceipt ? (int)substr($lastReceipt->receipt_number, -4) + 1 : 1;
                $receipt->receipt_number = "RCV-{$year}-" . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
            
            // Auto-calculate total cost
            if ($receipt->quantity_received && $receipt->unit_price) {
                $receipt->total_cost = $receipt->quantity_received * $receipt->unit_price;
            }
            
            if (!$receipt->received_at) {
                $receipt->received_at = now();
            }
        });
        
        static::created(function ($receipt) {
            // Add stock to inventory
            $receipt->medicine->addStock(
                $receipt->quantity_received,
                $receipt->received_by,
                "Stock Receipt: {$receipt->receipt_number}"
            );
            
            // Update request status if linked
            if ($receipt->restock_request_id) {
                $request = $receipt->restockRequest;
                
                $totalReceived = $request->receipts()->sum('quantity_received');
                
                if ($totalReceived >= $request->quantity_requested) {
                    $request->markAsReceived();
                } elseif ($totalReceived > 0) {
                    $request->update(['status' => 'Partially Received']);
                }
            }
        });
    }
    
    // ========================================
    // QUALITY CHECK HELPER
    // ========================================
    
    public function passedQualityCheck()
    {
        return $this->quality_status === 'Accepted' &&
               $this->packaging_intact &&
               $this->temperature_maintained &&
               $this->expiry_acceptable;
    }
}

