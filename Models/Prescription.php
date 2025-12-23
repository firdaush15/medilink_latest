<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $primaryKey = 'prescription_id';

    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'patient_id',
        'prescribed_date',
        'notes',
    ];

    protected $casts = [
        'prescribed_date' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function items()
    {
        return $this->hasMany(PrescriptionItem::class, 'prescription_id');
    }

    /**
     * ✅ ADDED: Relationship to prescription dispensing
     * This was the missing relationship causing the error
     */
    public function dispensing()
    {
        return $this->hasOne(PrescriptionDispensing::class, 'prescription_id', 'prescription_id');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Check if prescription has been dispensed
     */
    public function isDispensed()
    {
        return $this->dispensing && 
               $this->dispensing->verification_status === 'Dispensed';
    }

    /**
     * Check if prescription is pending dispensing
     */
    public function isPending()
    {
        return !$this->dispensing || 
               $this->dispensing->verification_status === 'Pending';
    }

    /**
     * Get total amount for dispensed items
     */
    public function getTotalAmount()
    {
        if (!$this->dispensing) {
            return 0;
        }

        return $this->dispensing->total_amount ?? 0;
    }

    /**
     * Check if prescription has been verified
     */
    public function isVerified()
    {
        return $this->dispensing && 
               in_array($this->dispensing->verification_status, ['Verified', 'Dispensed']);
    }
}