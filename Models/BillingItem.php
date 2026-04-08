<?php
// app/Models/BillingItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingItem extends Model
{
    protected $primaryKey = 'billing_item_id';
    
    protected $fillable = [
        'appointment_id',
        'item_type',
        'item_code',
        'description',
        'quantity',
        'unit_price',
        'amount',
        'medical_record_id',
        'prescription_id',
        'added_by',
    ];
    
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];
    
    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }
    
    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class, 'medical_record_id', 'record_id');
    }
    
    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id', 'prescription_id');
    }
    
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
    
    // ========================================
    // HELPER METHODS
    // ========================================
    
    public function getItemTypeDisplayAttribute()
    {
        return match ($this->item_type) {
            'consultation' => '💼 Consultation',
            'procedure' => '🔬 Procedure',
            'lab_test' => '🧪 Lab Test',
            'imaging' => '🩻 Imaging',
            'medication' => '💊 Medication',
            'medical_supply' => '🏥 Medical Supply',
            'other' => '📋 Other',
            default => ucfirst(str_replace('_', ' ', $this->item_type)),
        };
    }
    
    // ========================================
    // QUERY SCOPES
    // ========================================
    
    public function scopeForAppointment($query, $appointmentId)
    {
        return $query->where('appointment_id', $appointmentId);
    }
    
    public function scopeByType($query, $type)
    {
        return $query->where('item_type', $type);
    }
    
    public function scopeExcludingMedications($query)
    {
        return $query->where('item_type', '!=', 'medication');
    }
}

// ========================================
// app/Models/ProcedurePrice.php
// ========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedurePrice extends Model
{
    protected $primaryKey = 'procedure_id';
    
    protected $fillable = [
        'procedure_code',
        'procedure_name',
        'category',
        'description',
        'base_price',
        'is_active',
    ];
    
    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    // ========================================
    // QUERY SCOPES
    // ========================================
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
    
    // ========================================
    // HELPER METHODS
    // ========================================
    
    public function getCategoryDisplayAttribute()
    {
        return match ($this->category) {
            'consultation' => '💼 Consultation',
            'blood_test' => '🩸 Blood Test',
            'imaging' => '🩻 Imaging',
            'minor_procedure' => '🔬 Minor Procedure',
            'major_procedure' => '🏥 Major Procedure',
            'diagnostic_test' => '🧪 Diagnostic Test',
            'other' => '📋 Other',
            default => ucfirst(str_replace('_', ' ', $this->category)),
        };
    }
}