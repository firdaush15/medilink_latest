<?php
// app/Models/PrescriptionItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    protected $primaryKey = 'item_id';

    protected $fillable = [
        'prescription_id',
        'medicine_id',
        'medicine_name',
        'dosage',
        'frequency',
        'quantity_prescribed',
        'days_supply',
        'unit_price',
        'total_price',
        'quantity_dispensed',
        'batch_number',
        'expiry_date',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function medicine()
    {
        return $this->belongsTo(MedicineInventory::class, 'medicine_id');
    }

    // ========================================
    // HELPER METHODS
    // ========================================
    
    /**
     * Calculate total price when quantity changes
     */
    public function calculateTotalPrice()
    {
        $this->total_price = $this->unit_price * $this->quantity_prescribed;
        return $this->total_price;
    }

    /**
     * Check if fully dispensed
     */
    public function isFullyDispensed()
    {
        return $this->quantity_dispensed >= $this->quantity_prescribed;
    }

    /**
     * Get remaining quantity to dispense
     */
    public function getRemainingQuantity()
    {
        return $this->quantity_prescribed - $this->quantity_dispensed;
    }

    /**
     * Calculate days supply based on frequency
     * Example: "2 tablets 3 times daily" with 30 tablets = 5 days supply
     */
    public static function calculateDaysSupply($quantityPrescribed, $frequency)
    {
        // Extract numbers from frequency string
        // Example: "2 tablets 3 times daily" -> dose=2, times=3
        preg_match('/(\d+)\s*(?:tablet|capsule|ml)/i', $frequency, $doseMatches);
        preg_match('/(\d+)\s*times?\s*(?:daily|per day|a day)/i', $frequency, $timesMatches);
        
        $dosePerTime = isset($doseMatches[1]) ? (int)$doseMatches[1] : 1;
        $timesPerDay = isset($timesMatches[1]) ? (int)$timesMatches[1] : 1;
        
        $dailyDose = $dosePerTime * $timesPerDay;
        
        if ($dailyDose > 0) {
            return ceil($quantityPrescribed / $dailyDose);
        }
        
        return null;
    }

    /**
     * Format frequency for display
     */
    public function getFormattedFrequencyAttribute()
    {
        return ucfirst($this->frequency);
    }
}